#!/bin/bash

# Simple DoS Protection Test for uriegas-search_catalogs.php
# Tests basic protection features

echo "=========================================="
echo "Simple DoS Protection Test"
echo "=========================================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
BASE_URL="http://localhost:20505/uriegas-search_catalogs.php"
TOTAL_TESTS=0
PASSED_TESTS=0

# Helper functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
    PASSED_TESTS=$((PASSED_TESTS + 1))
}

print_failure() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

increment_test() {
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
}

# Test 1: Basic API Response
echo "1. Testing basic API response..."
increment_test
response=$(curl -s "${BASE_URL}?q=test" 2>/dev/null)
if echo "$response" | grep -q "success.*true"; then
    print_success "API responding normally"
else
    print_failure "API not responding correctly"
fi

# Test 2: DoS Protection Status
echo "2. Testing DoS protection status..."
increment_test
response=$(curl -s "${BASE_URL}?action=dos_status" 2>/dev/null)
if echo "$response" | grep -q "dos_protection"; then
    print_success "DoS protection is active"
else
    print_failure "DoS protection status not available"
fi

# Test 3: Large Request Protection
echo "3. Testing large request protection..."
increment_test
large_query=$(printf 'q=%*s' 5000 | tr ' ' 'a')
response=$(curl -s "${BASE_URL}?${large_query}" 2>/dev/null)
status_code=$(curl -s -o /dev/null -w "%{http_code}" "${BASE_URL}?${large_query}" 2>/dev/null)
if [ "$status_code" = "429" ]; then
    print_success "Large requests properly blocked"
else
    print_info "Large request returned HTTP $status_code (429 expected for blocking)"
fi

# Test 4: Rapid Requests Protection
echo "4. Testing rapid requests protection..."
increment_test
# Make 3 rapid requests
curl -s "${BASE_URL}?q=test1" > /dev/null 2>&1 &
sleep 0.1
curl -s "${BASE_URL}?q=test2" > /dev/null 2>&1 &
sleep 0.1
status_code=$(curl -s -o /dev/null -w "%{http_code}" "${BASE_URL}?q=test3" 2>/dev/null)
if [ "$status_code" = "429" ]; then
    print_success "Rapid requests properly blocked"
else
    print_info "Rapid request returned HTTP $status_code (may allow some rapid requests)"
fi

# Test 5: Normal Request After Delay
echo "5. Testing normal request after delay..."
increment_test
sleep 2  # Wait to reset rate limiting
response=$(curl -s "${BASE_URL}?q=normal" 2>/dev/null)
if echo "$response" | grep -q "success.*true"; then
    print_success "Normal requests work after delay"
else
    print_failure "Normal requests not working after delay"
fi

# Test 6: Security Headers
echo "6. Testing security headers..."
increment_test
headers=$(curl -s -I "${BASE_URL}?q=test" 2>/dev/null)
if echo "$headers" | grep -q "X-Content-Type-Options\|X-Frame-Options\|X-XSS-Protection"; then
    print_success "Security headers present"
else
    print_failure "Security headers missing"
fi

# Test 7: JSON Response Format
echo "7. Testing JSON response format..."
increment_test
response=$(curl -s "${BASE_URL}?q=test" 2>/dev/null)
if echo "$response" | python3 -m json.tool >/dev/null 2>&1; then
    print_success "Valid JSON response"
else
    print_failure "Invalid JSON response"
fi

# Summary
echo ""
echo "=========================================="
echo "DoS Protection Test Summary"
echo "=========================================="
echo "Total Tests: $TOTAL_TESTS"
echo "Passed: $PASSED_TESTS"
echo "Failed: $((TOTAL_TESTS - PASSED_TESTS))"

if [ $PASSED_TESTS -eq $TOTAL_TESTS ]; then
    echo -e "${GREEN}🎉 ALL DoS PROTECTION TESTS PASSED!${NC}"
    echo "Success Rate: 100%"
elif [ $PASSED_TESTS -ge $((TOTAL_TESTS * 80 / 100)) ]; then
    echo -e "${YELLOW}✅ DoS protection is working well${NC}"
    echo "Success Rate: $((PASSED_TESTS * 100 / TOTAL_TESTS))%"
else
    echo -e "${RED}⚠ Some DoS protection features may need improvement${NC}"
    echo "Success Rate: $((PASSED_TESTS * 100 / TOTAL_TESTS))%"
fi

echo ""
echo "Note: DoS protection uses simple session-based rate limiting."
echo "For production, consider implementing more advanced protection."