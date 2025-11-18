# Uriegas FAIR Catalog Search API Documentation

## Requirements
- PHP 7.4 or higher
- PostgreSQL 12 or higher
<!-- DEPRECATED:
- Sqlmap (for sql injection) -->

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

```
http://localhost:20505/uriegas-search_catalogs.php
```

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
curl "http://localhost:20505/uriegas-search_catalogs.php?q=healthcare"
```

### Paginated Search
```bash
# Get first 25 results
curl "http://localhost:20505/uriegas-search_catalogs.php?q=healthcare&limit=25"

# Get next 25 results (page 2)
curl "http://localhost:20505/uriegas-search_catalogs.php?q=healthcare&limit=25&offset=25"

# Get all results (no limit)
curl "http://localhost:20505/uriegas-search_catalogs.php?q=healthcare"
```

### Advanced Search with Filters
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?q=*&privacy=public&encryption=false&processed=true&date_from=2024-01-01"
```

### Get Available Usernames
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?action=get_usernames"
```

### Search Usernames
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?action=get_usernames&search=admin&limit=50"
```

### Search by Username (Exact Match)
Note: Username search now requires exact match. Use the get_usernames endpoint to get a list of available usernames first.
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?q=*&username=admin"
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

The API implements lightweight DoS (Denial of Service) protection designed to work alongside server-level protection:

### Protection Features
- **Request Size Limit**: Maximum 4KB per request
- **Rate Limiting**: Minimum 1 second between successive requests (session-based)
- **Simple Blocking**: Automatic blocking of oversized or too-frequent requests

### Protection Limits
- **Maximum request size**: 4KB (URI + headers + query string)
- **Minimum interval**: 1 second between requests from the same session
- **Protection method**: PHP session-based (lightweight)

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

## Security Measures

The API implements input validation and sanitization to prevent SQL injection attacks was tested using sqlmap. For that testing you can run the following command (changing sqlmap's location) is:

```bash
~/.local/bin/sqlmap -u "http://localhost:20505/uriegas-search_catalogs.php?q=test" --batch --level=5 --risk=3
```


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

**Note:** Simple DoS protection uses session-based tracking with no persistent files requiring maintenance.