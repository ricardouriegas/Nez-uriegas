#!/bin/bash

# API Test for uriegas-search_catalogs.php
# Tests the FAIR-compliant catalog search API

echo "=========================================="
echo "Uriegas Search Catalogs API Test"
echo "=========================================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Test counters
TOTAL_TESTS=0
PASSED_TESTS=0

# Function to print test results
test_result() {
    local test_name="$1"
    local result="$2"
    local details="$3"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    if [ "$result" = "PASS" ]; then
        echo -e "${GREEN}✓ PASS${NC} - $test_name"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}✗ FAIL${NC} - $test_name"
        if [ ! -z "$details" ]; then
            echo -e "  ${YELLOW}Details:${NC} $details"
        fi
    fi
}

# Get IP
my_ip=$(ip route get 8.8.8.8 | awk -F"src " 'NR==1{split($2,a," ");print a[1]}')
echo -e "${BLUE}Testing API at:${NC} http://${my_ip}:20505/uriegas-search_catalogs.php"

API_URL="http://${my_ip}:20505/uriegas-search_catalogs.php"

echo ""
echo "=========================================="
echo "API Endpoint Tests"
echo "=========================================="

# Test 1: API Accessibility
echo "1. Testing API accessibility..."
http_code=$(curl -s -o /dev/null -w "%{http_code}" "$API_URL" 2>/dev/null || echo "000")
if [ "$http_code" = "200" ]; then
    test_result "API endpoint accessible" "PASS" "HTTP $http_code"
else
    test_result "API endpoint accessible" "FAIL" "HTTP $http_code - API may be down"
fi

# Test 2: Empty search (no parameters)
echo "2. Testing empty search..."
response=$(curl -s "$API_URL" 2>/dev/null)
if echo "$response" | grep -q "Search term required"; then
    test_result "Empty search handling" "PASS"
else
    test_result "Empty search handling" "FAIL" "Should require search term"
fi

# Test 3: Basic search with query parameter
echo "3. Testing basic search..."
response=$(curl -s "${API_URL}?q=test" 2>/dev/null)
if echo "$response" | grep -q -E "\"success\".*true|\"results\"|\["; then
    test_result "Basic search functionality" "PASS"
else
    test_result "Basic search functionality" "FAIL" "Should return search results"
fi

# Test 4: JSON response format
echo "4. Testing JSON response format..."
response=$(curl -s "$API_URL" 2>/dev/null)
if echo "$response" | python3 -m json.tool >/dev/null 2>&1; then
    test_result "Valid JSON response" "PASS"
else
    test_result "Valid JSON response" "FAIL" "Response is not valid JSON"
fi

# Test 5: HTTP method restrictions
echo "5. Testing HTTP method restrictions..."
post_response=$(curl -s -X POST "$API_URL" 2>/dev/null)
if echo "$post_response" | grep -q -E "error|not allowed|method"; then
    test_result "POST method rejection" "PASS"
else
    test_result "POST method rejection" "FAIL" "Should only allow GET method"
fi

put_response=$(curl -s -X PUT "$API_URL" 2>/dev/null)
if echo "$put_response" | grep -q -E "error|not allowed|method"; then
    test_result "PUT method rejection" "PASS"
else
    test_result "PUT method rejection" "FAIL" "Should only allow GET method"
fi

echo ""
echo "=========================================="
echo "Security Tests"
echo "=========================================="

# Test 6: SQL Injection Protection
echo "6. Testing SQL injection protection..."
sql_payload_encoded="%27%3B%20DROP%20TABLE%20catalogs%3B%20--"
response=$(curl -s "${API_URL}?q=${sql_payload_encoded}" 2>/dev/null)
# Check that response is successful and doesn't contain unescaped SQL
if echo "$response" | grep -q -E "\"success\".*true.*\"results\"" && ! echo "$response" | grep -q "'; DROP TABLE"; then
    test_result "SQL injection protection" "PASS"
else
    test_result "SQL injection protection" "FAIL" "May be vulnerable to SQL injection"
fi

# Test 7: XSS Protection
echo "7. Testing XSS protection..."
xss_payload_encoded="%3Cscript%3Ealert%28%27test%27%29%3C%2Fscript%3E"
response=$(curl -s "${API_URL}?q=${xss_payload_encoded}" 2>/dev/null)
if echo "$response" | grep -q -E "\"success\".*true.*\"results\"" && ! echo "$response" | grep -q "<script>"; then
    test_result "XSS protection" "PASS"
else
    test_result "XSS protection" "FAIL" "May be vulnerable to XSS"
fi

# Test 8: Path Traversal Protection
echo "8. Testing path traversal protection..."
path_payload_encoded="../../../etc/passwd"
response=$(curl -s "${API_URL}?q=${path_payload_encoded}" 2>/dev/null)
if echo "$response" | grep -q -E "\"success\".*true.*\"results\"" && ! echo "$response" | grep -q "root:"; then
    test_result "Path traversal protection" "PASS"
else
    test_result "Path traversal protection" "FAIL" "May be vulnerable to path traversal"
fi

echo ""
echo "=========================================="
echo "Parameter Validation Tests"
echo "=========================================="

# Test 9: Special characters in search query
echo "9. Testing special characters..."
special_chars_encoded="%21%40%23%24%25%5E%26%2A%28%29%2B%3D%5B%5D%7B%7D%7C%3B%3A%2C.%3C%3E%3F"
response=$(curl -s "${API_URL}?q=${special_chars_encoded}" 2>/dev/null)
if echo "$response" | grep -q -E "\"success\".*true.*\"results\""; then
    test_result "Special characters handling" "PASS"
else
    test_result "Special characters handling" "FAIL" "Should handle special characters safely"
fi

# Test 10: Very long search query
echo "10. Testing very long query..."
long_query=$(printf 'a%.0s' {1..1000})
response=$(curl -s "${API_URL}?q=${long_query}" 2>/dev/null)
if echo "$response" | grep -q -E "\"success\".*true|\"results\"|\["; then
    test_result "Long query handling" "PASS"
else
    test_result "Long query handling" "FAIL" "Should handle long queries safely"
fi

# Test 11: Filter parameters
echo "11. Testing filter parameters..."
response=$(curl -s "${API_URL}?q=test&privacy=public&encryption=false" 2>/dev/null)
if echo "$response" | grep -q -E "\"success\".*true|\"results\"|\["; then
    test_result "Filter parameters" "PASS"
else
    test_result "Filter parameters" "FAIL" "Should handle filter parameters"
fi

# Test 12: Date range filters
echo "12. Testing date filters..."
response=$(curl -s "${API_URL}?q=test&date_from=2024-01-01&date_to=2024-12-31" 2>/dev/null)
if echo "$response" | grep -q -E "\"success\".*true|\"results\"|\["; then
    test_result "Date range filters" "PASS"
else
    test_result "Date range filters" "FAIL" "Should handle date range filters"
fi

echo ""
echo "=========================================="
echo "Response Structure Tests"
echo "=========================================="

# Test 13: Response structure
echo "13. Testing response structure..."
response=$(curl -s "$API_URL?q=test" 2>/dev/null)
if echo "$response" | grep -q "\"success\"" && echo "$response" | grep -q "\"results\"" && echo "$response" | grep -q "\"fair_compliant\""; then
    test_result "Response structure" "PASS"
else
    test_result "Response structure" "FAIL" "Response should have proper FAIR structure"
fi

# Test 14: Content-Type header
echo "14. Testing Content-Type header..."
content_type=$(curl -s -I "$API_URL" 2>/dev/null | grep -i "content-type" | grep -i "json")
if [ ! -z "$content_type" ]; then
    test_result "JSON Content-Type header" "PASS"
else
    test_result "JSON Content-Type header" "FAIL" "Should return application/json content type"
fi

echo ""
echo "=========================================="
echo "FAIR Compliance Tests"
echo "=========================================="

# Test 15: Findable - Search functionality
echo "15. Testing Findable principle..."
response=$(curl -s "${API_URL}?q=test" 2>/dev/null)
if echo "$response" | grep -q -E "\"results\"|\[.*\]"; then
    test_result "Findable - Search functionality" "PASS"
else
    test_result "Findable - Search functionality" "FAIL" "Should provide searchable results"
fi

# Test 16: Accessible - Public access
echo "16. Testing Accessible principle..."
response=$(curl -s "${API_URL}?q=test&privacy=public" 2>/dev/null)
if echo "$response" | grep -q -E "\"success\".*true.*\"results\""; then
    test_result "Accessible - Public access" "PASS"
else
    test_result "Accessible - Public access" "FAIL" "Should provide access to public catalogs"
fi

echo ""
echo "=========================================="
echo "Test Summary"
echo "=========================================="
echo "Total Tests: $TOTAL_TESTS"
echo "Passed: $PASSED_TESTS"
echo "Failed: $((TOTAL_TESTS - PASSED_TESTS))"

if [ $PASSED_TESTS -eq $TOTAL_TESTS ]; then
    echo -e "${GREEN}🎉 ALL API TESTS PASSED!${NC}"
    echo -e "${GREEN}The uriegas-search_catalogs.php API is working correctly.${NC}"
    SUCCESS_RATE=100
else
    echo -e "${YELLOW}⚠ Some tests failed - see details above.${NC}"
    echo -e "${BLUE}ℹ Note: Some failures may be expected if the system is not fully configured.${NC}"
    SUCCESS_RATE=$(( (PASSED_TESTS * 100) / TOTAL_TESTS ))
fi

echo "Success Rate: ${SUCCESS_RATE}%"

echo ""
echo "=========================================="
echo "Manual Testing Examples"
echo "=========================================="
echo "You can manually test the API with:"
echo ""
echo "# Basic search:"
echo "curl \"${API_URL}?q=your_search_term\""
echo ""
echo "# Search with filters:"
echo "curl \"${API_URL}?q=test&privacy=public&encryption=false\""
echo ""
echo "# Search with user authentication:"
echo "curl \"${API_URL}?q=test&user_id=YOUR_USER_TOKEN\""
echo ""
echo "# Search with date range:"
echo "curl \"${API_URL}?q=test&date_from=2024-01-01&date_to=2024-12-31\""
echo ""
echo "# Pretty print JSON response:"
echo "curl \"${API_URL}?q=test\" | python3 -m json.tool"
echo ""
echo "Note: To get real catalog data, run the project setup scripts first:"
echo "cd /home/richy/Nez-uriegas/services/ && ./configure.sh && ./uriegas-more-catalogos.sh"