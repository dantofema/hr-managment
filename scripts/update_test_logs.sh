#!/bin/bash

# Script to update failing test logs for error resolution
# Usage: ./scripts/update_test_logs.sh

LOGS_DIR="agents/logs"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

echo "Updating test failure logs..."

# Function to clean ANSI color codes from text
clean_ansi() {
    sed 's/\x1b\[[0-9;]*m//g'
}

# Function to extract detailed error information from Cypress logs
extract_cypress_error_details() {
    local error_file="$1"
    local output_file="$2"

    if [ ! -f "$error_file" ]; then
        echo "No Cypress error log found" >> "$output_file"
        return
    fi

    echo "FAILING TESTS DETECTED:" >> "$output_file"
    echo "======================" >> "$output_file"
    echo "" >> "$output_file"

    # Process the error log to extract meaningful information
    local current_error=""
    local error_count=0

    while IFS= read -r line; do
        clean_line=$(echo "$line" | clean_ansi)

        # Detect start of new error
        if echo "$clean_line" | grep -q "failing"; then
            if [ -n "$current_error" ] && [ $error_count -lt 5 ]; then
                echo "$current_error" >> "$output_file"
                echo "----------------------------------------" >> "$output_file"
                echo "" >> "$output_file"
                ((error_count++))
            fi
            current_error="ERROR #$((error_count + 1)):"$'\n'
            current_error+="Failing tests count: $clean_line"$'\n'
        fi

        # Extract CypressError details
        if echo "$clean_line" | grep -q "CypressError:"; then
            error_description=$(echo "$clean_line" | sed 's/.*CypressError: //')
            current_error+="Description: $error_description"$'\n'
        fi

        # Extract URL information
        if echo "$clean_line" | grep -q "failed trying to load:"; then
            next_line_url=$(echo "$clean_line" | grep -o "http[s]*://[^[:space:]]*" || echo "URL not captured")
            if [ "$next_line_url" != "URL not captured" ]; then
                current_error+="Failed URL: $next_line_url"$'\n'
            fi
        fi

        # Extract status code information
        if echo "$clean_line" | grep -q "status code was not"; then
            current_error+="Issue: $clean_line"$'\n'
        fi

        # Extract content-type issues
        if echo "$clean_line" | grep -q "content-type:"; then
            current_error+="Issue: $clean_line"$'\n'
        fi

        # Extract test suite information
        if echo "$clean_line" | grep -q "in the current suite:"; then
            suite_name=$(echo "$clean_line" | sed 's/.*current suite: //' | sed 's/`//g')
            current_error+="Test Suite: $suite_name"$'\n'
        fi

        # Extract stack trace location
        if echo "$clean_line" | grep -q "at.*cypress_runner.js"; then
            stack_info=$(echo "$clean_line" | sed 's/.*at //')
            current_error+="Stack trace: $stack_info"$'\n'
        fi

        # Extract test file information
        if echo "$clean_line" | grep -q "\.png\|\.spec\."; then
            test_info=$(echo "$clean_line" | grep -o "[^[:space:]]*\.png\|[^[:space:]]*\.spec\.[^[:space:]]*")
            if [ -n "$test_info" ]; then
                current_error+="Test file/screenshot: $test_info"$'\n'
            fi
        fi

    done < "$error_file"

    # Add the last error if exists
    if [ -n "$current_error" ] && [ $error_count -lt 5 ]; then
        echo "$current_error" >> "$output_file"
        echo "----------------------------------------" >> "$output_file"
    fi
}

# Function to run PHPUnit and capture detailed error information
capture_phpunit_errors() {
    local output_file="$1"
    
    echo "PHPUNIT ERROR ANALYSIS:" >> "$output_file"
    echo "======================" >> "$output_file"
    echo "" >> "$output_file"
    
    # Run PHPUnit with verbose output to capture real errors using Docker
    echo "Running PHPUnit via Docker to capture current errors..." >> "$output_file"
    echo "" >> "$output_file"
    
    # Create temporary file for PHPUnit output
    local temp_output="/tmp/phpunit_detailed_output.log"
    
    # Check if Docker containers are running
    if ! docker-compose ps | grep -q "app.*Up"; then
        echo "ERROR: Docker app container is not running!" >> "$output_file"
        echo "Please start the containers with: docker-compose up -d" >> "$output_file"
        echo "" >> "$output_file"
        return
    fi
    
    # Run PHPUnit via Docker and capture both stdout and stderr
    if docker-compose exec -T app vendor/bin/phpunit --verbose --stop-on-failure --no-coverage 2>&1 | tee "$temp_output"; then
        echo "All PHPUnit tests are passing!" >> "$output_file"
    else
        echo "DETECTED FAILING TESTS:" >> "$output_file"
        echo "----------------------" >> "$output_file"
        echo "" >> "$output_file"
        
        # Parse the PHPUnit output for specific error information
        local error_count=0
        local in_failure=false
        local current_test=""
        local current_error=""
        
        while IFS= read -r line; do
            # Detect test failure start
            if echo "$line" | grep -q "FAILURES!"; then
                in_failure=true
                continue
            fi
            
            # Detect individual test failure
            if echo "$line" | grep -q "^[0-9]) "; then
                if [ -n "$current_test" ] && [ $error_count -lt 3 ]; then
                    echo "FAILURE #$((error_count + 1)):" >> "$output_file"
                    echo "Test: $current_test" >> "$output_file"
                    echo "Error: $current_error" >> "$output_file"
                    echo "----------------------------------------" >> "$output_file"
                    echo "" >> "$output_file"
                    ((error_count++))
                fi
                
                current_test=$(echo "$line" | sed 's/^[0-9]) //')
                current_error=""
            fi
            
            # Capture error messages
            if echo "$line" | grep -qE "(Failed asserting|Exception|Error|Call to undefined)"; then
                current_error="$line"
            fi
            
            # Capture file and line information
            if echo "$line" | grep -q "^/.*\.php:[0-9]"; then
                file_line=$(echo "$line" | grep -o "/.*\.php:[0-9]*")
                if [ -n "$current_error" ]; then
                    current_error+=" (at $file_line)"
                else
                    current_error="Error at $file_line"
                fi
            fi
            
            # Capture stack trace (first few lines)
            if echo "$line" | grep -q "^#[0-9]"; then
                if [ $error_count -lt 3 ]; then
                    current_error+=" | Stack: $(echo "$line" | head -c 100)"
                fi
            fi
            
        done < "$temp_output"
        
        # Add the last error if exists
        if [ -n "$current_test" ] && [ $error_count -lt 3 ]; then
            echo "FAILURE #$((error_count + 1)):" >> "$output_file"
            echo "Test: $current_test" >> "$output_file"
            echo "Error: $current_error" >> "$output_file"
            echo "----------------------------------------" >> "$output_file"
        fi
        
        # Also check for common database/setup issues
        echo "" >> "$output_file"
        echo "COMMON ISSUES DETECTED:" >> "$output_file"
        echo "----------------------" >> "$output_file"
        
        if grep -q "database\|connection\|PDO" "$temp_output"; then
            echo "• Database connection issues detected" >> "$output_file"
        fi
        
        if grep -q "Class.*not found\|autoload" "$temp_output"; then
            echo "• Autoloading/Class loading issues detected" >> "$output_file"
        fi
        
        if grep -q "fixtures\|setUp" "$temp_output"; then
            echo "• Test setup/fixtures issues detected" >> "$output_file"
        fi
    fi
    
    # Clean up
    rm -f "$temp_output"
}

# Function to run Cypress and capture detailed error information
capture_cypress_errors() {
    local output_file="$1"
    
    echo "CYPRESS ERROR ANALYSIS:" >> "$output_file"
    echo "=======================" >> "$output_file"
    echo "" >> "$output_file"
    
    # Check if there's an existing cypress errors log first
    if [ -f "$LOGS_DIR/cypress_errors.log" ]; then
        echo "Found existing Cypress error log, analyzing..." >> "$output_file"
        extract_cypress_error_details "$LOGS_DIR/cypress_errors.log" "$output_file"
    fi
    
    # Also try to run Cypress via Docker if containers are running
    echo "" >> "$output_file"
    echo "ATTEMPTING LIVE CYPRESS RUN:" >> "$output_file"
    echo "----------------------------" >> "$output_file"
    
    # Check if frontend container is running
    if ! docker-compose ps | grep -q "frontend.*Up"; then
        echo "WARNING: Frontend container is not running!" >> "$output_file"
        echo "Please start with: docker-compose up -d frontend" >> "$output_file"
        echo "" >> "$output_file"
    else
        # Create temporary file for Cypress output
        local temp_cypress="/tmp/cypress_live_output.log"
        
        echo "Running Cypress tests via Docker..." >> "$output_file"
        
        # Run Cypress headless via Docker (limit to prevent hanging)
        if timeout 120 docker-compose exec -T frontend npx cypress run --headless --browser electron 2>&1 | head -100 > "$temp_cypress"; then
            echo "Cypress tests completed successfully!" >> "$output_file"
        else
            echo "" >> "$output_file"
            echo "LIVE CYPRESS ERRORS DETECTED:" >> "$output_file"
            echo "-----------------------------" >> "$output_file"
            
            # Extract meaningful errors from live run
            if [ -f "$temp_cypress" ]; then
                grep -E "(failing|CypressError|Error:|failed)" "$temp_cypress" | head -10 | clean_ansi >> "$output_file"
            fi
        fi
        
        # Clean up
        rm -f "$temp_cypress"
    fi
}

# Function to update Cypress failing tests log
update_cypress_logs() {
    echo "=== CYPRESS FAILING TESTS LOG ===" > "$LOGS_DIR/cypress_failing_tests.log"
    echo "Generated: $TIMESTAMP" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "Analysis: Detailed error extraction with stack traces" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "" >> "$LOGS_DIR/cypress_failing_tests.log"
    
    capture_cypress_errors "$LOGS_DIR/cypress_failing_tests.log"

    echo "" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "RECOMMENDED ACTIONS:" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "===================" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "1. Check if containers are running: docker-compose ps" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "2. Start frontend container: docker-compose up -d frontend" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "3. Run Cypress via Docker: docker-compose exec frontend npx cypress run" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "4. Check if backend API is accessible from frontend container" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "5. Verify the base URL in cypress.config.js" >> "$LOGS_DIR/cypress_failing_tests.log"
    echo "6. Check browser console for additional errors" >> "$LOGS_DIR/cypress_failing_tests.log"
}

# Function to update PHPUnit failing tests log
update_phpunit_logs() {
    echo "=== PHPUNIT FAILING TESTS LOG ===" > "$LOGS_DIR/phpunit_failing_tests.log"
    echo "Generated: $TIMESTAMP" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "Analysis: Live test execution with detailed error capture via Docker" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "" >> "$LOGS_DIR/phpunit_failing_tests.log"
    
    capture_phpunit_errors "$LOGS_DIR/phpunit_failing_tests.log"

    echo "" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "RECOMMENDED ACTIONS:" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "===================" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "1. Run specific failing test: docker-compose exec app vendor/bin/phpunit --filter TestMethodName" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "2. Check containers status: docker-compose ps" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "3. Check database connection: docker-compose exec app php bin/console doctrine:schema:validate" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "4. Verify test environment configuration in phpunit.xml.dist" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "5. Review test fixtures and data setup" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "6. Run with more verbosity: docker-compose exec app vendor/bin/phpunit --verbose --debug" >> "$LOGS_DIR/phpunit_failing_tests.log"
    echo "7. Check application logs: docker-compose logs app" >> "$LOGS_DIR/phpunit_failing_tests.log"
}

# Create logs directory if it doesn't exist
mkdir -p "$LOGS_DIR"

# Update both log types
echo "Analyzing Cypress errors..."
update_cypress_logs

echo "Analyzing PHPUnit errors..."
update_phpunit_logs

echo ""
echo "Test failure logs updated successfully!"
echo "Files created/updated:"
echo "- $LOGS_DIR/cypress_failing_tests.log (with detailed error descriptions and traces)"
echo "- $LOGS_DIR/phpunit_failing_tests.log (with live test execution and stack traces)"
echo ""
echo "You can now review the detailed error information in the agents/logs/ directory."
