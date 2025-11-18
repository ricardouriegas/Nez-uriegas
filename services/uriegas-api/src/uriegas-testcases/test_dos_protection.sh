#!/bin/bash

# DoS Protection Test for uriegas-search_catalogs.php
# Tests rate limiting, request size limits, and protection features

echo "=========================================="
echo "DoS Protection Test for Search Catalogs API"
echo "=========================================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
BASE_URL="http://localhost:20506/uriegas-search_catalogs.php"

echo -e "${BLUE}Testing containerized API at:${NC} $BASE_URL"

# Check if container is running
echo ""
echo "Checking if uriegas-api container is running..."
if docker ps --format "table {{.Names}}" | grep -q "services-uriegas-api-1\|uriegas-api"; then
    echo -e "${GREEN}✓ uriegas-api container is running${NC}"
else
    echo -e "${RED}✗ uriegas-api container is not running${NC}"
    echo -e "${YELLOW}Please run: cd /home/richy/Nez-uriegas/services && docker-compose up -d uriegas-api${NC}"
    exit 1
fi
TEST_RESULTS=()
TOTAL_TESTS=0
PASSED_TESTS=0

# Helper functions
print_test_header() {
    echo -e "\n${BLUE}=== $1 ===${NC}"
}

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

# Test function to make HTTP requests
make_request() {
    local url="$1"
    local expected_status="$2"
    local timeout="${3:-5}"
    
    response=$(curl -s -w "\n%{http_code}" --max-time "$timeout" "$url" 2>/dev/null)
    
    if [ $? -eq 0 ]; then
        body=$(echo "$response" | head -n -1)
        status=$(echo "$response" | tail -n 1)
        
        echo "STATUS:$status"
        echo "BODY:$body"
    else
        echo "STATUS:000"
        echo "BODY:{\"error\":\"Request failed\"}"
    fi
}

# Test 1: Basic API Response
test_basic_api_response() {
    print_test_header "Test 1: Basic API Response"
    increment_test
    
    result=$(make_request "${BASE_URL}?q=test" "200")
    
    status=$(echo "$result" | grep "STATUS:" | cut -d: -f2)
    body=$(echo "$result" | grep "BODY:" | cut -d: -f2-)
    
    if [ "$status" = "200" ]; then
        if echo "$body" | grep -q "success.*true"; then
            print_success "API responding normally"
        else
            print_failure "API returned error response"
        fi
    else
        print_failure "API failed (HTTP $status)"
    fi
}

# Test 2: DoS Protection Status
test_dos_protection_status() {
    print_test_header "Test 2: DoS Protection Status"
    increment_test
    
    status_url="${BASE_URL}?action=dos_status"
    result=$(make_request "$status_url" "200")
    
    status=$(echo "$result" | grep "STATUS:" | cut -d: -f2)
    body=$(echo "$result" | grep "BODY:" | cut -d: -f2-)
    
    if [ "$status" = "200" ]; then
        if echo "$body" | grep -q "dos_protection"; then
            print_success "DoS protection is active"
        else
            print_failure "DoS protection status endpoint returned unexpected response"
        fi
    else
        print_failure "DoS protection status endpoint failed (HTTP $status)"
    fi
}

# Test 3: Normal Search Request
test_normal_request() {
    print_test_header "Test 3: Normal Search Request"
    increment_test
    
    search_url="${BASE_URL}?q=catalog"
    result=$(make_request "$search_url" "200")
    
    status=$(echo "$result" | grep "STATUS:" | cut -d: -f2)
    body=$(echo "$result" | grep "BODY:" | cut -d: -f2-)
    
    if [ "$status" = "200" ]; then
        if echo "$body" | grep -q "success.*true"; then
            print_success "Normal search request successful"
            total_results=$(echo "$body" | jq -r '.search_metadata.total_results' 2>/dev/null || echo "unknown")
            print_info "Found $total_results catalogs"
        else
            print_failure "Normal search request returned error: $body"
        fi
    else
        print_failure "Normal search request failed (HTTP $status)"
    fi
}

# Test 3: Rapid Requests (Rate Limiting)
test_rate_limiting() {
    print_test_header "Test 3: Rapid Requests (Rate Limiting Test)"
    print_info "Sending rapid requests to trigger rate limiting..."
    
    search_url="${BASE_URL}?q=test"
    rate_limited=false
    
    for i in $(seq 1 35); do
        result=$(make_request "$search_url" "200")
        status=$(echo "$result" | grep "STATUS:" | cut -d: -f2)
        body=$(echo "$result" | grep "BODY:" | cut -d: -f2-)
        
        printf "Request %2d: HTTP %s" "$i" "$status"
        
        if [ "$status" = "429" ]; then
            echo -e " - ${RED}RATE LIMITED${NC}"
            if echo "$body" | grep -q "DOS_PROTECTION_ACTIVE"; then
                print_success "Rate limiting triggered correctly"
                rate_limited=true
                increment_test
            fi
            break
        elif [ "$status" = "200" ]; then
            echo -e " - ${GREEN}Success${NC}"
        else
            echo -e " - ${YELLOW}Error${NC}"
        fi
        
        # Small delay between requests
        sleep 0.1
    done
    
    if [ "$rate_limited" = false ]; then
        increment_test
        print_failure "Rate limiting was not triggered after 35 requests"
    fi
}

# Test 4: Large Request Test
test_large_request() {
    print_test_header "Test 4: Large Request Test"
    increment_test
    
    # Create a query string larger than 8KB
    large_query=$(printf 'a%.0s' {1..9000})
    large_url="${BASE_URL}?q=${large_query}"
    
    print_info "Sending request with 9KB query (exceeds 8KB limit)..."
    
    result=$(make_request "$large_url" "429")
    status=$(echo "$result" | grep "STATUS:" | cut -d: -f2)
    body=$(echo "$result" | grep "BODY:" | cut -d: -f2-)
    
    if [ "$status" = "429" ]; then
        if echo "$body" | grep -q "Request size exceeds limit\|DOS_PROTECTION_ACTIVE"; then
            print_success "Large request blocked correctly"
        else
            print_failure "Large request blocked but wrong error message"
        fi
    else
        print_failure "Large request was not blocked (HTTP $status)"
    fi
}

# Test 5: Request Size Validation
test_request_sizes() {
    print_test_header "Test 5: Request Size Validation"
    
    # Test normal size request
    increment_test
    normal_query="catalog"
    normal_url="${BASE_URL}?q=${normal_query}"
    
    result=$(make_request "$normal_url" "200")
    status=$(echo "$result" | grep "STATUS:" | cut -d: -f2)
    
    if [ "$status" = "200" ]; then
        print_success "Normal size request accepted"
    else
        print_failure "Normal size request rejected (HTTP $status)"
    fi
    
    # Test borderline size request (around 7KB)
    increment_test
    borderline_query=$(printf 'a%.0s' {1..7000})
    borderline_url="${BASE_URL}?q=${borderline_query}"
    
    result=$(make_request "$borderline_url" "200")
    status=$(echo "$result" | grep "STATUS:" | cut -d: -f2)
    
    if [ "$status" = "200" ] || [ "$status" = "400" ]; then
        print_success "Borderline size request handled appropriately (HTTP $status)"
    else
        print_failure "Borderline size request unexpected response (HTTP $status)"
    fi
}

# Test 7: Security Headers Check
test_security_headers() {
    print_test_header "Test 7: Security Headers Check"
    increment_test
    
    headers=$(curl -s -I "${BASE_URL}?q=test" 2>/dev/null)
    
    security_headers_found=0
    
    if echo "$headers" | grep -qi "X-Content-Type-Options:"; then
        print_success "X-Content-Type-Options header present"
        security_headers_found=$((security_headers_found + 1))
    else
        print_failure "X-Content-Type-Options header missing"
    fi
    
    if echo "$headers" | grep -qi "X-Frame-Options:"; then
        print_success "X-Frame-Options header present"
        security_headers_found=$((security_headers_found + 1))
    else
        print_failure "X-Frame-Options header missing"
    fi
    
    if echo "$headers" | grep -qi "X-XSS-Protection:"; then
        print_success "X-XSS-Protection header present"
        security_headers_found=$((security_headers_found + 1))
    else
        print_failure "X-XSS-Protection header missing"
    fi
    
    if [ $security_headers_found -ge 2 ]; then
        print_success "Security headers implementation adequate"
    else
        print_failure "Insufficient security headers implemented"
    fi
}

# Test 8: Rapid Successive Requests (1-second limit)
test_rapid_successive() {
    print_test_header "Test 8: Rapid Successive Requests (1-second limit)"
    increment_test
    
    search_url="${BASE_URL}?q=rapid"
    
    print_info "Making rapid successive requests (< 1 second apart)..."
    
    # First request
    result1=$(make_request "$search_url" "200")
    status1=$(echo "$result1" | grep "STATUS:" | cut -d: -f2)
    
    # Immediate second request (should be blocked)
    result2=$(make_request "$search_url" "429")
    status2=$(echo "$result2" | grep "STATUS:" | cut -d: -f2)
    body2=$(echo "$result2" | grep "BODY:" | cut -d: -f2-)
    
    if [ "$status1" = "200" ] && [ "$status2" = "429" ]; then
        if echo "$body2" | grep -q "too frequent\|rapid"; then
            print_success "Rapid successive requests blocked correctly"
        else
            print_success "Rapid successive requests blocked (different error message)"
        fi
    elif [ "$status2" = "429" ]; then
        print_success "Rapid successive requests blocked (may be combined with other limits)"
    else
        print_failure "Rapid successive requests not blocked properly ($status1, $status2)"
    fi
}

# Main test execution
main() {
    echo "Starting DoS Protection Tests..."
    echo "Base URL: $BASE_URL"
    echo "Timestamp: $(date)"
    echo ""
    
    # Check if jq is available for JSON parsing
    if ! command -v jq &> /dev/null; then
        print_info "jq not found - JSON responses will be shown as raw text"
    fi
    
    # Run all tests
    test_basic_api_response
    test_dos_protection_status
    test_normal_request
    test_security_headers
    test_request_sizes
    test_rapid_successive
    test_rate_limiting
    
    # Final summary
    echo ""
    echo "=========================================="
    echo "DoS Protection Test Summary"
    echo "=========================================="
    echo -e "Total Tests: ${BLUE}$TOTAL_TESTS${NC}"
    echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}"
    echo -e "Failed: ${RED}$((TOTAL_TESTS - PASSED_TESTS))${NC}"
    
    if [ $PASSED_TESTS -eq $TOTAL_TESTS ]; then
        echo -e "\n${GREEN}✓ All DoS protection tests passed!${NC}"
        echo "The API has robust DoS protection implemented."
    else
        echo -e "\n${YELLOW}⚠ Some tests failed.${NC}"
        echo "Review the failed tests to improve DoS protection."
    fi
    
    echo ""
    echo "Note: Check the API logs for detailed protection activity."
    echo "Rate limit files: rate_limits.json and blacklist.json"
}

main "$@"
