# Uriegas FAIR Catalog Search API Documentation

## Overview

The Uriegas FAIR Catalog Search API is a containerized, FAIR-compliant (Findable, Accessible, Interoperable, Reusable) search service for catalog management. It provides secure, cross-database queries with built-in DoS protection and comprehensive security measures.

## Requirements

### System Requirements
- Docker and Docker Compose
- PHP 8.0 or higher (containerized)
- PostgreSQL 12 or higher (containerized)

### Required Services
The API requires the following containerized services:
- `uriegas-api`: Main API containe### Log Retention Recommendations

- **Security Logs:** Retain for 90 days minimum for forensic analysis
- **DoS Protection Logs:** Retain for 30 days for pattern analysis
- **Database Logs:** Retain for 7 days for operational monitoring

**Note:** Simple DoS protection uses session-based tracking with no persistent files requiring maintenance.

## Container Deployment

### Docker Compose Configuration

The API is deployed as part of a multi-container application using Docker Compose.

**Service Definition:**
```yaml
uriegas-api:
  build:
    context: ./uriegas-api
    dockerfile: Dockerfile
  image: uriegas-api:latest
  depends_on:
    - db_metadata
    - db_pub_sub
    - db_auth
  ports:
    - "20506:80"
  environment:
    # Database connections
    DB_USER: muyalmanager
    DB_PASSWORD: f0l34lraSoTRumoGitRo
    DB_HOST: db_metadata
    DB_NAME: multi
    DB_PORT: 5432
    # Additional database passwords
    PUB_SUB_DB_PASSWORD: sicuhowradRaxi5R2ke6
    AUTH_DB_PASSWORD: niCi7unamltrubrlJusp
    # API configuration
    API_LOG_LEVEL: INFO
    API_TIMEZONE: America/Mexico_City
    URL: "http://localhost:20506"
  volumes:
    - ./uriegas-api/src/:/var/www/html/
    - ./uriegas-api/logs:/var/www/html/logs
  restart: always
  healthcheck:
    test: ["CMD", "curl", "-f", "http://localhost/uriegas-search_catalogs.php?action=dos_status"]
    interval: 30s
    timeout: 10s
    retries: 3
```

### Container Management Commands

```bash
# Start the API container and dependencies
docker-compose up -d uriegas-api

# View container logs
docker logs services-uriegas-api-1

# Follow logs in real-time
docker logs -f services-uriegas-api-1

# Check container status
docker ps | grep uriegas-api

# Stop the container
docker-compose stop uriegas-api

# Restart the container
docker-compose restart uriegas-api

# Rebuild the container (after code changes)
docker-compose build uriegas-api
docker-compose up -d uriegas-api

# View resource usage
docker stats services-uriegas-api-1

# Execute commands inside the container
docker exec -it services-uriegas-api-1 bash

# Check container health
docker inspect services-uriegas-api-1 | grep -A 10 Health
```

### Troubleshooting Container Issues

#### Container Won't Start
```bash
# Check container logs for errors
docker logs services-uriegas-api-1

# Verify database containers are running
docker ps | grep -E "db_metadata|db_pub_sub|db_auth"

# Check Docker Compose configuration
docker-compose config

# Remove and recreate the container
docker-compose rm -f uriegas-api
docker-compose up -d uriegas-api
```

#### Database Connection Errors
```bash
# Test database connectivity from within container
docker exec services-uriegas-api-1 nc -zv db_metadata 5432
docker exec services-uriegas-api-1 nc -zv db_pub_sub 5432
docker exec services-uriegas-api-1 nc -zv db_auth 5432

# Check environment variables
docker exec services-uriegas-api-1 env | grep DB
```

#### API Not Responding
```bash
# Check if Apache is running inside container
docker exec services-uriegas-api-1 service apache2 status

# Test from inside the container
docker exec services-uriegas-api-1 curl -s http://localhost/uriegas-search_catalogs.php?action=dos_status

# Check port binding
docker port services-uriegas-api-1

# Verify firewall/network settings
curl -v http://localhost:20506/uriegas-search_catalogs.php?action=dos_status
```

### Container Networking

The API container communicates with three database containers:
- **db_metadata**: File metadata storage
- **db_pub_sub**: Catalog and subscription data
- **db_auth**: User authentication

All containers are on the same Docker bridge network, allowing internal hostname resolution (e.g., `db_metadata`, `db_pub_sub`, `db_auth`).

### Volume Mounts

**Source Code:** `./uriegas-api/src/` → `/var/www/html/`
- Live code updates without container rebuild (development)
- Change PHP files and see immediate results

**Logs:** `./uriegas-api/logs` → `/var/www/html/logs`
- Access logs from host machine
- Persistent logging across container restarts

### Health Checks

The container includes an automated health check that:
- Runs every 30 seconds
- Queries the DoS protection status endpoint
- Marks container as unhealthy after 3 failed checks
- Automatically visible in `docker ps` output

```bash
# View health status
docker ps --format "table {{.Names}}\t{{.Status}}"

# View detailed health check logs
docker inspect services-uriegas-api-1 --format='{{json .State.Health}}' | python3 -m json.tool
```

## Production Deployment Considerations

### Environment Variables

Ensure these environment variables are properly set in production:

```bash
# Database Configuration
DB_USER=<database_username>
DB_PASSWORD=<strong_password>
DB_HOST=<database_host>
DB_NAME=<database_name>
DB_PORT=5432

# Cross-Database Access
PUB_SUB_DB_PASSWORD=<pub_sub_password>
AUTH_DB_PASSWORD=<auth_password>

# API Configuration
API_LOG_LEVEL=INFO  # Use ERROR in production
API_TIMEZONE=<your_timezone>
URL=<your_production_url>
```

### Security Checklist

- [ ] Change all default database passwords
- [ ] Restrict CORS origins in production (change from `*` to specific domains)
- [ ] Enable HTTPS with SSL/TLS certificates
- [ ] Implement IP-based rate limiting for stateless clients
- [ ] Set up log rotation for security and application logs
- [ ] Configure firewall rules to restrict database access
- [ ] Regular security updates for container base images
- [ ] Monitor DoS protection logs for abuse patterns
- [ ] Implement API key authentication for sensitive operations
- [ ] Set up container resource limits (CPU, memory)

### Performance Optimization

```yaml
# Add resource limits to docker-compose.yml
resources:
  limits:
    cpus: '2'
    memory: 1G
  reservations:
    cpus: '1'
    memory: 512M
```

### Backup Strategy

```bash
# Backup mounted volumes
tar -czf uriegas-api-backup-$(date +%Y%m%d).tar.gz \
  ./uriegas-api/src/ \
  ./uriegas-api/logs/

# Backup Docker image
docker save uriegas-api:latest | gzip > uriegas-api-image.tar.gz

# Restore from backup
docker load < uriegas-api-image.tar.gz
```

## Recent Updates and Improvements

### Version 2.0 (November 2025)

#### Container Migration
- **Dockerization**: Complete migration to containerized deployment
- **Port Change**: Updated from port 20505 to 20506
- **Multi-Database Architecture**: Integrated cross-database queries with db_metadata, db_pub_sub, and db_auth
- **Health Checks**: Added automated container health monitoring
- **Volume Mounts**: Live code updates and persistent logging

#### Security Enhancements
- **CORS Preflight Support**: Proper handling of OPTIONS requests for browser compatibility
- **Session-Based DoS Protection**: Lightweight rate limiting using PHP sessions
- **Enhanced Input Validation**: Strict validation for usernames, file types, and all user inputs
- **Prepared Statements**: 100% coverage for SQL injection prevention
- **Security Headers**: Complete set of security headers (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection)

#### Testing Improvements
- **Comprehensive Test Suite**: Three complete test scripts with 100% pass rates
- **Container Awareness**: Tests automatically check if containers are running
- **Session-Aware Testing**: curl commands properly maintain sessions for rate limit testing
- **Color-Coded Output**: Easy-to-read test results with visual feedback
- **Automated Validation**: Tests for security, FAIR compliance, and DoS protection

#### API Enhancements
- **Username Search**: Exact match username filtering with get_usernames endpoint
- **File Type Filtering**: Cross-database file type search capability
- **Flexible Pagination**: Remove hardcoded limits, support custom page sizes
- **Enhanced Metadata**: Owner usernames included in catalog results
- **FAIR Compliance**: Full implementation of Findable, Accessible, Interoperable, Reusable principles

#### Documentation Updates
- **Container Deployment Guide**: Complete Docker Compose configuration and management
- **Testing Documentation**: Detailed test suite descriptions and usage
- **Troubleshooting Section**: Common issues and solutions for containerized environment
- **Session-Based Protection Explanation**: Clear documentation of how rate limiting works
- **Production Checklist**: Security and deployment best practices

### Breaking Changes from Version 1.x

1. **Port Number**: Changed from 20505 to 20506
2. **Base URL**: Now requires container infrastructure
3. **Session Requirement**: Rate limiting requires session cookies (affects stateless clients)
4. **Database Architecture**: Now requires three separate database containers
5. **Environment Variables**: New required variables for cross-database access

### Migration Guide from Version 1.x

```bash
# 1. Update all API client URLs
# Old: http://localhost:20505/uriegas-search_catalogs.php
# New: http://localhost:20506/uriegas-search_catalogs.php

# 2. Start required database containers
docker-compose up -d db_metadata db_pub_sub db_auth

# 3. Build and start the API container
docker-compose build uriegas-api
docker-compose up -d uriegas-api

# 4. Verify deployment
curl "http://localhost:20506/uriegas-search_catalogs.php?action=dos_status"

# 5. Run test suites to validate
bash /home/richy/Nez-uriegas/services/uriegas-api/src/uriegas-testcases/test_search_catalogs.sh
```

## Contributing

### Development Workflow

```bash
# 1. Make changes to source code in ./uriegas-api/src/
# 2. Changes are immediately reflected (volume mount)
# 3. Test changes
curl "http://localhost:20506/uriegas-search_catalogs.php?q=test"

# 4. Run test suites
bash ./uriegas-api/src/uriegas-testcases/test_search_catalogs.sh

# 5. Check logs for errors
docker logs services-uriegas-api-1

# 6. If Dockerfile changed, rebuild
docker-compose build uriegas-api
docker-compose up -d uriegas-api
```

### Code Standards

- **PHP Standards**: Follow PSR-12 coding standards
- **Security First**: Always use prepared statements, never concatenate SQL
- **Input Validation**: Validate and sanitize all user inputs
- **Error Handling**: Catch exceptions and return proper HTTP status codes
- **Logging**: Log security events, errors, and important operations
- **Comments**: Document complex logic and security considerations
- **Testing**: Add test cases for new features

### Adding New Features

1. **Update PHP Code**: Add feature to `uriegas-search_catalogs.php`
2. **Add Tests**: Create or update test cases in `uriegas-testcases/`
3. **Update Documentation**: Document new endpoints and parameters
4. **Test Thoroughly**: Run all test suites and verify 100% pass rate
5. **Security Review**: Consider security implications
6. **Update CHANGELOG**: Document changes for version tracking

## Support and Contact

For issues, questions, or contributions:

- **GitHub Repository**: [ricardouriegas/Nez-uriegas](https://github.com/ricardouriegas/Nez-uriegas)
- **Branch**: `uriegas-with-one-container`
- **API Location**: `/services/uriegas-api/`
- **Test Location**: `/services/uriegas-api/src/uriegas-testcases/`

### Common Support Questions

**Q: Why is my rate limiting test failing?**
A: Ensure you're using curl with cookie files (`-c` and `-b` flags) to maintain session state.

**Q: Container won't start - connection refused**
A: Verify all three database containers (db_metadata, db_pub_sub, db_auth) are running first.

**Q: Changes to PHP files not reflecting**
A: Volume mounts should show immediate changes. Try `docker-compose restart uriegas-api`.

**Q: How do I enable debug logging?**
A: Set `API_LOG_LEVEL=DEBUG` in docker-compose.yml environment variables.

**Q: Can I use this API without Docker?**
A: Not recommended. The API is designed for containerized deployment with specific database networking.

---

**Last Updated**: November 18, 2025  
**API Version**: 2.0  
**Documentation Version**: 2.0  
**Container**: `uriegas-api:latest` 20506)
- `db_pub_sub`: PostgreSQL database for catalog data
- `db_metadata`: PostgreSQL database for file metadata
- `db_auth`: PostgreSQL database for user authentication

### Quick Start

```bash
# Start all required services
cd /home/richy/Nez-uriegas/services
docker-compose up -d uriegas-api

# Verify the container is running
docker ps | grep uriegas-api

# Check API health
curl "http://localhost:20506/uriegas-search_catalogs.php?action=dos_status"
```

## Endpoints

### Search Catalogs
```
GET /uriegas-search_catalogs.php
```

### Get Available Usernames
```
GET /uriegas-search_catalogs.php?action=get_usernames
```

### Check DoS Protection Status
```
GET /uriegas-search_catalogs.php?action=dos_status
```

**Optional Parameters:**
- `search`: Filter usernames containing this text (case insensitive)
- `limit`: Maximum number of usernames to return (default: 100, max: 100)

**Note:** To search by username, first call the `get_usernames` endpoint to retrieve a list of available usernames, then use the exact username in your search query. The username filter now requires exact matches only.

## Base URL

The API runs in a Docker container and is accessible at:

```
http://localhost:20506/uriegas-search_catalogs.php
```

**Container Configuration:**
- Container Name: `services-uriegas-api-1` or `uriegas-api`
- Internal Port: 80
- External Port: 20506
- Network: Docker bridge network with access to database containers

**Production URLs may vary depending on your deployment configuration.**

## Request Parameters

### Required Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | Search term (minimum 2 characters). Use `*` for wildcard search to retrieve all catalogs. |

### Optional Parameters

| Parameter | Type | Description | Example Values |
|-----------|------|-------------|----------------|
| `privacy` | string | Filter by privacy status | `public`, `private` |
| `encryption` | string | Filter by encryption status | `true`, `false` |
| `processed` | string | Filter by processing status | `true`, `false` |
| `date_from` | string | Filter catalogs created from this date | `2024-01-01` |
| `date_to` | string | Filter catalogs created until this date | `2024-12-31` |
| `file_type` | string | Filter by file type (alphanumeric only) | `pdf`, `json`, `csv` |
| `username` | string | Filter by owner username (exact match) | `admin`, `user123` |
| `limit` | integer | Maximum number of results to return | `50`, `100`, `500` |
| `offset` | integer | Number of results to skip (for pagination) | `0`, `50`, `100` |
| `action` | string | Special action to perform | `get_usernames`, `dos_status` |

### Pagination Parameters

The API now supports flexible pagination without hardcoded limits:

- **No Limits**: If `limit` is not specified, all matching results are returned
- **Custom Limits**: Specify any positive integer for `limit`
- **Offset Support**: Use `offset` for pagination (e.g., `offset=50&limit=50` for page 2)
- **Unlimited Results**: Removed the previous 100-result limitation

## Example Requests

### Basic Search
```bash
curl "http://localhost:20506/uriegas-search_catalogs.php?q=healthcare"
```

### Paginated Search
```bash
# Get first 25 results
curl "http://localhost:20506/uriegas-search_catalogs.php?q=healthcare&limit=25"

# Get next 25 results (page 2)
curl "http://localhost:20506/uriegas-search_catalogs.php?q=healthcare&limit=25&offset=25"

# Get all results (no limit)
curl "http://localhost:20506/uriegas-search_catalogs.php?q=healthcare"
```

### Advanced Search with Filters
```bash
curl "http://localhost:20506/uriegas-search_catalogs.php?q=*&privacy=public&encryption=false&processed=true&date_from=2024-01-01"
```

### Get Available Usernames
```bash
curl "http://localhost:20506/uriegas-search_catalogs.php?action=get_usernames"
```

### Search Usernames
```bash
curl "http://localhost:20506/uriegas-search_catalogs.php?action=get_usernames&search=admin&limit=50"
```

### Search by Username (Exact Match)
Note: Username search now requires exact match. Use the get_usernames endpoint to get a list of available usernames first.
```bash
curl "http://localhost:20506/uriegas-search_catalogs.php?q=*&username=admin"
```

## Response Format

### Success Response

```json
{
  "success": true,
  "fair_compliant": true,
  "search_metadata": {
    "search_term": "healthcare",
    "filters_applied": {
      "privacy": "public",
      "encryption": "false"
    },
    "timestamp": "2024-10-06T15:30:45+00:00",
    "total_results": 110,
    "returned_results": 50,
    "limited": true,
    "pagination": {
      "limit": 50,
      "offset": 0,
      "has_more": true
    }
  },
  "repository_statistics": {
    "total_catalogs": 150,
    "public_catalogs": 120,
    "private_catalogs": 30,
    "encrypted_catalogs": 75,
    "processed_catalogs": 140
  },
  "results": {
    "catalogs": [
      {
        "namecatalog": "Healthcare Data Collection 2024",
        "created_at": "2024-03-15T10:30:00Z",
        "owner_username": "dr_smith",
        "dispersemode": "replicated",
        "encryption": true,
        "isprivate": false,
        "processed": true,
        "file_count": 25,
        "group": "medical_research",
        "fair_findable": {
          "indexed": true,
          "searchable_fields": ["namecatalog"],
          "created_timestamp": "2024-03-15T10:30:00Z"
        },
        "fair_accessible": {
          "is_public": true,
          "access_protocol": "HTTP/HTTPS",
          "authentication_required": false
        },
        "fair_interoperable": {
          "format_standard": "PostgreSQL/JSON",
          "dispersal_method": "replicated",
          "encryption_standard": "ABE"
        },
        "fair_reusable": {
          "license": "institutional",
          "processing_status": "ready",
          "file_count": 25,
          "metadata_complete": true
        }
      }
    ]
  },
  "fair_principles": {
    "findable": "Rich metadata with searchable fields",
    "accessible": "Access control aware with user context",
    "interoperable": "Standardized JSON/PostgreSQL format",
    "reusable": "Complete provenance and processing metadata"
  }
}
```

### Get Usernames Response

```json
{
  "success": true,
  "usernames": [
    "admin",
    "dr_smith",
    "researcher01",
    "user123"
  ],
  "total_count": 4,
  "search_term": "",
  "limit_applied": 100
}
```

### Error Response

```json
{
  "success": false,
  "error": "Search term required (minimum 2 characters)",
  "fair_compliant": false
}
```

### Search Metadata Fields

The `search_metadata` object contains important information about the search results:

| Field | Type | Description |
|-------|------|-------------|
| `total_results` | integer | Total number of catalogs matching the search criteria |
| `returned_results` | integer | Number of catalogs returned in this response |
| `limited` | boolean | `true` if there are more results than returned (pagination available) |
| `search_term` | string | The search term used for the query |
| `filters_applied` | object | Applied filters (only non-empty filters included) |
| `timestamp` | string | ISO 8601 timestamp when the search was performed |
| `pagination` | object | Pagination information (when applicable) |

### Pagination Object Fields

| Field | Type | Description |
|-------|------|-------------|
| `limit` | integer/null | Maximum results requested (null if no limit) |
| `offset` | integer | Number of results skipped |
| `has_more` | boolean | `true` if more results are available beyond current page |

**Note:** When `limited` is `true` and `total_results` > `returned_results`, it means there are more catalogs available but the API limits results to 100 per request for performance.

## Catalog Object Properties

### Core Properties

| Property | Type | Description |
|----------|------|-------------|
| `namecatalog` | string | Human-readable catalog name |
| `created_at` | string | ISO 8601 timestamp of catalog creation |
| `owner_username` | string | Username of the catalog owner |
| `dispersemode` | string | Data distribution method (e.g., "replicated", "dispersed") |
| `encryption` | boolean | Whether the catalog is encrypted |
| `isprivate` | boolean | Whether the catalog is private |
| `processed` | boolean | Whether the catalog has been processed |
| `file_count` | integer | Number of files in the catalog |
| `group` | string | Group/category the catalog belongs to |

## Error Handling

### Common Error Codes

| Error Message | Cause | Solution |
|--------------|-------|----------|
| "Search term required (minimum 2 characters)" | Empty or too short search term | Provide a search term with at least 2 characters |
| "Only GET method allowed" | Using POST/PUT/DELETE | Use GET method only |
| "Database connection failed" | Database connectivity issues | Check database connectivity |

## Simple DoS Protection

The API implements lightweight, session-based DoS (Denial of Service) protection designed to work alongside server-level protection:

### Protection Features
- **Request Size Limit**: Maximum 4KB per request
- **Session-Based Rate Limiting**: Minimum 1 second between successive requests from the same session
- **Simple Blocking**: Automatic blocking of oversized or too-frequent requests
- **Zero External Dependencies**: No Redis or file-based storage required

### How Session-Based Protection Works

The API uses PHP sessions to track request timing per user session:

1. **Session Tracking**: Each user's session maintains a timestamp of their last request
2. **Rate Check**: New requests are compared against the last request timestamp
3. **Blocking**: Requests within 1 second of the previous request are blocked with HTTP 429
4. **Session Reset**: After 1 second, the session is allowed to make new requests

**Important Note for Testing:**
- **Browser/Client Behavior**: Browsers automatically maintain sessions via cookies
- **curl Behavior**: By default, each curl command creates a new session (requests appear independent)
- **Testing Rate Limiting**: Use curl's `-c` (save cookies) and `-b` (send cookies) flags to maintain session state

### Testing with Session Persistence

```bash
# Create a temporary cookie file
COOKIE_FILE=$(mktemp)

# First request (will succeed)
curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" \
  "http://localhost:20506/uriegas-search_catalogs.php?q=test1"

# Immediate second request (will be blocked with HTTP 429)
curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" \
  "http://localhost:20506/uriegas-search_catalogs.php?q=test2"

# Clean up
rm -f "$COOKIE_FILE"
```

### Protection Limits
- **Maximum request size**: 4KB (URI + headers + query string)
- **Minimum interval**: 1 second between requests from the same session
- **Protection method**: PHP session-based (lightweight, no file I/O)
- **Scope**: Per-session (not IP-based)

### DoS Protection Status Check
```
GET /uriegas-search_catalogs.php?action=dos_status
```

Returns current DoS protection information:
```json
{
  "success": true,
  "dos_protection": {
    "active": true,
    "type": "Simple Session-based",
    "max_request_size_kb": 4,
    "min_request_interval_seconds": 1
  },
  "server_info": {
    "timestamp": "2025-10-29T17:30:45+00:00",
    "protection_level": "Basic"
  }
}
```

### Error Response for DoS Protection
When protection is triggered, the API returns HTTP 429:
```json
{
  "success": false,
  "error": "Request blocked due to abuse prevention",
  "code": "REQUEST_BLOCKED"
}
```

### Design Philosophy
This simplified DoS protection focuses on:
- **Minimal overhead** for normal operations
- **Basic protection** against simple abuse
- **Server-level reliance** for sophisticated attacks
- **Session-based tracking** (no complex file storage)
- **Zero external dependencies** (no Redis/JSON files)
- **Stateless-friendly**: Each new connection/session gets independent rate limiting

**Why Session-Based?**
- Lightweight and fast (uses existing PHP session infrastructure)
- No additional services or file I/O required
- Automatically cleaned up by PHP garbage collection
- Perfect for containerized environments
- Works seamlessly with browser-based clients

**Limitations:**
- Stateless API clients (like curl without cookies) bypass rate limiting
- Not effective against distributed attacks from multiple IPs
- Relies on browser/client maintaining session cookies
- Session data is per-container (not shared across instances)

For production environments with stateless clients or distributed attacks, consider implementing:
- IP-based rate limiting with Redis
- API key-based throttling
- Web Application Firewall (WAF)
- Load balancer-level rate limiting

## Security Measures

The API implements comprehensive input validation and sanitization to prevent common web vulnerabilities:

### Security Features
- **Prepared Statements**: All database queries use PDO prepared statements to prevent SQL injection
- **Input Validation**: All user inputs are validated and sanitized before processing
- **CORS Headers**: Proper CORS configuration for cross-origin requests with preflight support
- **Security Headers**: X-Content-Type-Options, X-Frame-Options, X-XSS-Protection
- **Input Length Limits**: Maximum 1000 characters per input field
- **File Type Validation**: Strict alphanumeric-only validation for file extensions
- **Username Format Validation**: Strict pattern matching for username searches

### SQL Injection Testing

The API has been tested against SQL injection attacks using sqlmap. To test the security:

```bash
# Install sqlmap if needed
pip install sqlmap

# Run comprehensive SQL injection test
sqlmap -u "http://localhost:20506/uriegas-search_catalogs.php?q=test" \
  --batch --level=5 --risk=3
```

**Expected Result:** No SQL injection vulnerabilities should be found due to the use of prepared statements throughout the codebase.

### CORS Configuration

The API implements enhanced CORS headers for browser compatibility:

```php
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, OPTIONS
Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With
Access-Control-Max-Age: 86400
```

**OPTIONS Preflight Handling:**
The API properly handles CORS preflight requests. When a browser makes a cross-origin request:
1. Browser sends an OPTIONS request to check permissions
2. API responds with CORS headers and HTTP 200
3. Browser proceeds with the actual GET request
4. API returns the data with CORS headers

This ensures seamless integration with frontend applications running on different domains or ports.


## Logging and Monitoring

The API generates logs for security monitoring, debugging, and system administration.

### Log Files and Locations

#### 1. Security Logs (`search_security.log`)
**Purpose:** Monitor input validation and potential security threats
**Locations tried (in order):**
- `/tmp/search_security.log`
- `<api_directory>/search_security.log`
- `/var/log/search_security.log`

**Log Format:** JSON entries, one per line
```json
{
  "timestamp": "2025-10-29T11:22:05-06:00",
  "event": "input_validation",
  "input_length": 12,
  "client_ip": "172.18.0.1",
  "user_agent": "curl/8.11.1",
  "request_uri": "/uriegas-search_catalogs.php?q=test"
}
```

#### 2. Simple DoS Protection Logs
**Purpose:** Track basic protection events
**Log Entry Examples:**
```
Large request blocked from: 172.18.0.1
Rapid requests blocked from: 172.18.0.1
```

#### 3. Database Connection Logs
**Purpose:** Monitor database connectivity and issues
**Log Entry Examples:**
```
Pub_Sub DB connection failed: SQLSTATE[08006] [7] could not connect to server
Auth DB connection successful
Auth DB connection failed: SQLSTATE[08006] [7] could not connect to server
Warning: Auth database not available - username search will be disabled
```

#### 4. Search Operation Logs
**Purpose:** Track search operations and performance
**Log Entry Examples:**
```
Username search for 'testuser' found 2 tokens
Retrieved usernames for 5 tokens
File type search initiated for extension: pdf
Found 150 files with extension: pdf
File type search completed. Found 45 catalogs with extension: pdf
File type search failed for extension 'invalidext': Invalid file type format
Invalid username format: user@invalid
Warning: Auth database not available - username search skipped
```

### Log Monitoring and Management

#### Simple Protection Data
- DoS protection uses PHP sessions (no persistent files)
- Logs are managed by the system Log class
- No complex rate limiting files to maintain

#### Key Events to Monitor

**Security Events:**
- Input validation failures
- Suspicious user agents
- Unusual request patterns
- File type search attempts with invalid extensions

**Performance Events:**
- Database connection failures
- Cross-database query performance
- Large result set processing

**DoS Protection Events:**
- Large request blocking
- Rapid request detection
- Basic abuse prevention

#### Log Analysis Examples

**Find DoS Protection Events:**
```bash
grep "blocked from" /path/to/log/file
```

**Monitor File Type Searches:**
```bash
grep "File type search" /path/to/log/file
```

**Check Database Connection Issues:**
```bash
grep "connection failed" /path/to/log/file
```

**Monitor Security Events:**
```bash
jq '.event' /tmp/search_security.log | sort | uniq -c
```

### Troubleshooting with Logs

#### Common Issues and Log Patterns

**1. DoS Protection Triggered:**
```
Large request blocked from: [IP]
Rapid requests blocked from: [IP]
```
*Solution:* Check if legitimate traffic, client may need to slow down requests

**2. Database Connectivity:**
```
Pub_Sub DB connection failed: [error details]
```
*Solution:* Verify database service status and connection parameters

**3. Cross-Database Queries Failing:**
```
Username search failed: [error details]
File type search failed: [error details]
```
*Solution:* Check auth and metadata database connectivity

**4. Excessive Security Events:**
```json
{"event": "input_validation", "input_length": 1500, ...}
```
*Solution:* Monitor for potential attacks or malformed requests

### Log Retention Recommendations

- **Security Logs:** Retain for 90 days minimum for forensic analysis
- **DoS Protection Logs:** Retain for 30 days for pattern analysis
- **Database Logs:** Retain for 7 days for operational monitoring

**Note:** Simple DoS protection uses PHP sessions (no persistent files requiring maintenance).

## Testing

The API includes comprehensive test suites located in `src/uriegas-testcases/`.

### Available Test Suites

#### 1. Comprehensive API Test (`test_search_catalogs.sh`)
Tests all API functionality including search, filtering, security, and FAIR compliance.

```bash
bash /home/richy/Nez-uriegas/services/uriegas-api/src/uriegas-testcases/test_search_catalogs.sh
```

**Tests Included:**
- API endpoint accessibility
- Empty search handling
- Basic search functionality
- JSON response format validation
- HTTP method restrictions (POST/PUT rejection)
- SQL injection protection
- XSS protection
- Path traversal protection
- Special characters handling
- Long query handling
- Filter parameters
- Date range filters
- Response structure validation
- Content-Type header validation
- FAIR principle compliance (Findable & Accessible)

**Expected Result:** 100% pass rate (17/17 tests)

#### 2. Simple DoS Protection Test (`test_dos_simple.sh`)
Quick validation of basic DoS protection features.

```bash
bash /home/richy/Nez-uriegas/services/uriegas-api/src/uriegas-testcases/test_dos_simple.sh
```

**Tests Included:**
- Basic API response
- DoS protection status
- Large request protection (>4KB)
- Rapid request protection (session-based)
- Normal request after delay
- Security headers presence
- JSON response format

**Expected Result:** 100% pass rate (7/7 tests)

**Note:** This test uses session-aware curl commands with cookie files to properly test rate limiting.

#### 3. Comprehensive DoS Protection Test (`test_dos_protection.sh`)
Detailed testing of all DoS protection mechanisms.

```bash
bash /home/richy/Nez-uriegas/services/uriegas-api/src/uriegas-testcases/test_dos_protection.sh
```

**Tests Included:**
- Basic API response
- DoS protection status endpoint
- Normal search requests
- Security headers validation
- Request size validation (normal and borderline)
- Rapid successive requests (session-based)
- Extended rate limiting test (35 rapid requests)

**Expected Result:** 100% pass rate (8/8 tests)

### Running All Tests

```bash
# Navigate to the services directory
cd /home/richy/Nez-uriegas/services

# Run all test suites
bash ./uriegas-api/src/uriegas-testcases/test_search_catalogs.sh
bash ./uriegas-api/src/uriegas-testcases/test_dos_simple.sh
bash ./uriegas-api/src/uriegas-testcases/test_dos_protection.sh
```

### Test Prerequisites

All tests automatically:
1. **Check Container Status**: Verify the `uriegas-api` container is running
2. **Exit Gracefully**: Provide helpful error messages if the container is down
3. **Use Correct Endpoint**: Target `http://localhost:20506`

**If tests fail with connection errors:**
```bash
# Start the API container
cd /home/richy/Nez-uriegas/services
docker-compose up -d uriegas-api

# Verify it's running
docker ps | grep uriegas-api

# Check container logs
docker logs services-uriegas-api-1
```

### Manual Testing Examples

```bash
# Test basic search
curl "http://localhost:20506/uriegas-search_catalogs.php?q=test"

# Test with filters
curl "http://localhost:20506/uriegas-search_catalogs.php?q=test&privacy=public"

# Test DoS protection status
curl "http://localhost:20506/uriegas-search_catalogs.php?action=dos_status"

# Test get usernames
curl "http://localhost:20506/uriegas-search_catalogs.php?action=get_usernames"

# Pretty print JSON response
curl "http://localhost:20506/uriegas-search_catalogs.php?q=test" | python3 -m json.tool
```

### Understanding Test Results

**Color-Coded Output:**
- 🟢 **Green (✓)**: Test passed
- 🔴 **Red (✗)**: Test failed
- 🟡 **Yellow (ℹ)**: Informational message

**Success Rates:**
- **100%**: All tests passed (production ready)
- **85%+**: Good (minor issues, review failures)
- **<85%**: Needs attention (significant issues found)