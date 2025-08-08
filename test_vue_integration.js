// Simple Node.js script to test Vue.js integration
const { execSync } = require('child_process');

console.log('🧪 Testing Vue.js Integration...\n');

try {
    // Test 1: Check if the home page loads
    console.log('1. Testing home page accessibility...');
    const response = execSync('curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/', { encoding: 'utf8' });
    if (response.trim() === '200') {
        console.log('   ✅ Home page loads successfully (HTTP 200)');
    } else {
        console.log(`   ❌ Home page failed (HTTP ${response.trim()})`);
        process.exit(1);
    }

    // Test 2: Check if Vue.js app container exists
    console.log('2. Testing Vue.js app container...');
    const htmlContent = execSync('curl -s http://localhost:8000/', { encoding: 'utf8' });
    if (htmlContent.includes('<div id="app"')) {
        console.log('   ✅ Vue.js app container found');
    } else {
        console.log('   ❌ Vue.js app container not found');
        process.exit(1);
    }

    // Test 3: Check if JavaScript file is included
    console.log('3. Testing JavaScript file inclusion...');
    if (htmlContent.includes('src="/js/app.js"')) {
        console.log('   ✅ Vue.js JavaScript file is included');
    } else {
        console.log('   ❌ Vue.js JavaScript file not found');
        process.exit(1);
    }

    // Test 4: Check if JavaScript file exists and is accessible
    console.log('4. Testing JavaScript file accessibility...');
    const jsResponse = execSync('curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/js/app.js', { encoding: 'utf8' });
    if (jsResponse.trim() === '200') {
        console.log('   ✅ JavaScript file is accessible (HTTP 200)');
    } else {
        console.log(`   ❌ JavaScript file not accessible (HTTP ${jsResponse.trim()})`);
        process.exit(1);
    }

    // Test 5: Check if CSS file is accessible
    console.log('5. Testing CSS file accessibility...');
    const cssResponse = execSync('curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/css/app.css', { encoding: 'utf8' });
    if (cssResponse.trim() === '200') {
        console.log('   ✅ CSS file is accessible (HTTP 200)');
    } else {
        console.log(`   ❌ CSS file not accessible (HTTP ${cssResponse.trim()})`);
        process.exit(1);
    }

    // Test 6: Check if Vue.js content is in the JavaScript bundle
    console.log('6. Testing Vue.js content in JavaScript bundle...');
    const jsContent = execSync('curl -s http://localhost:8000/js/app.js', { encoding: 'utf8' });
    if (jsContent.includes('Vue') || jsContent.includes('createApp') || jsContent.includes('Counter')) {
        console.log('   ✅ Vue.js content found in JavaScript bundle');
    } else {
        console.log('   ❌ Vue.js content not found in JavaScript bundle');
        process.exit(1);
    }

    console.log('\n🎉 All Vue.js integration tests passed!');
    console.log('\n📋 Summary:');
    console.log('   • Home page loads successfully');
    console.log('   • Vue.js app container is present');
    console.log('   • JavaScript and CSS files are accessible');
    console.log('   • Vue.js content is compiled in the bundle');
    console.log('\n✨ Vue.js integration is working correctly!');
    console.log('   Visit http://localhost:8000/ to see the counter component in action.');

} catch (error) {
    console.error('❌ Test failed:', error.message);
    process.exit(1);
}