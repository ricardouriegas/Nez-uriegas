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
- `uriegas-api`: Main API container
- `db_auth`: User authentication database
- `db_pub_sub`: Publication metadata database
- `db_metadata`: Catalog metadata database

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

## Error Handling

### Common Error Codes

| Error Message | Cause | Solution |
|--------------|-------|----------|
| "Search term required (minimum 2 characters)" | Empty or too short search term | Provide a search term with at least 2 characters |
| "Only GET method allowed" | Using POST/PUT/DELETE | Use GET method only |
| "Database connection failed" | Database connectivity issues | Check database connectivity |

## Log Retention Recommendations

- **Security Logs:** Retain for 90 days minimum for forensic analysis
- **DoS Protection Logs:** Retain for 30 days for pattern analysis
- **Database Logs:** Retain for 7 days for operational monitoring

> **Note:** Simple DoS protection uses session-based tracking with no persistent files requiring maintenance.