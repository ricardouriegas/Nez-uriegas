# How Nez Follows the FAIR Principles

This document explains clearly how the Nez system implements the FAIR principles (Findable, Accessible, Interoperable, Reusable) in catalog and file management.

## What are the FAIR Principles?

The FAIR principles are a set of guidelines to make data more useful and easy to use. FAIR stands for:

- **F**indable: Data can be easily found
- **A**ccessible: Data can be accessed when needed
- **I**nteroperable: Data works with other systems
- **R**eusable: Data can be used in different contexts

## How Nez Implements Each Principle

### 1. Findable

**What does it mean?**

Users must be able to search for and find the catalogs they need.

**How Nez does it:**

- **Text search**: Users can search for catalogs by typing words related to the catalog name
- **Unique identifiers**: Each catalog has a unique code that identifies it (tokencatalog and keycatalog)
- **Search filters**: You can search by file type, creation date, owner, and other characteristics
- **Date recording**: All catalogs store the date they were created to sort them by time

**Practical example:**

If a user searches for "health", the system finds all catalogs that have that word in their name, regardless of whether it's in uppercase or lowercase.

### 2. Accessible

**What does it mean?**

Data must be available to those who have permission to view it.

**How Nez does it:**

- **Privacy control**: Catalogs can be public (everyone sees them) or private (only the owner)
- **Permission system**: The system verifies who is the owner of each catalog
- **Standard protocol**: Uses HTTP/HTTPS, protocols that work in any browser or application
- **Access verification**: Before showing results, the system checks if the catalog is public

**Practical example:**

When someone performs a search, by default they only see public catalogs. Private catalogs only appear if the user is the owner.

### 3. Interoperable

**What does it mean?**

Data must be able to work with other systems and applications.

**How Nez does it:**

- **JSON format**: System responses use JSON, a format understood by most modern applications
- **Standard dates**: Dates follow the ISO 8601 format (example: 2024-03-15T10:30:00Z), internationally recognized
- **Standard database**: Uses PostgreSQL, a widely used database system
- **REST API**: Follows REST conventions for communication between systems
- **Cross-references**: Catalogs connect across different databases using unique codes

**Practical example:**

A developer can connect another application (web, mobile, desktop) to the Nez system without needing special adapters, because it uses standard formats and protocols.

### 4. Reusable

**What does it mean?**

Data must have enough information so it can be used in different situations.

**How Nez does it:**

- **Complete metadata**: Each catalog includes information about who created it, when, if it's encrypted, if it's private, etc.
- **Processing status**: Indicates if the catalog has already been processed and is ready to use
- **File counter**: Shows how many files each catalog contains
- **Encryption information**: Indicates if files are protected with encryption
- **Dispersion method**: Explains how files are stored (replicated, dispersed)
- **Identified owner**: Shows the username of the catalog owner

**Practical example:**

If a user finds an interesting catalog, they can see all its information: how many files it has, who the owner is, if it's encrypted, when it was created, etc. This helps decide if that catalog serves their needs.

## Benefits of Following FAIR Principles in Nez

### For Users:

- Find catalogs faster using searches and filters
- See only information they have permission to view
- Obtain complete details about each catalog

### For Administrators:

- The system connects easily with other tools
- Data is organized with unique identifiers
- There are records of all operations for monitoring

### For Developers:

- Can integrate Nez with other systems using standard formats
- The REST API facilitates the creation of new applications
- Technical documentation explains how to use each function

## Complete FAIR Search Example

**Situation:** A user searches for catalogs about "research" created in March 2024.

**FAIR Process:**

1. **Findable**: The system searches in catalog names and applies the date filter
2. **Accessible**: Only shows public catalogs or private catalogs of the user (if the user has permission)
3. **Interoperable**: Returns results in standard JSON format with ISO 8601 dates
4. **Reusable**: Each result includes complete metadata (owner, files, encryption, status)

**Result:** The user receives a list of catalogs that match their search, with all the necessary information to decide which one to use.

## Security in FAIR Implementation

Nez implements FAIR with security measures:

- **Input validation**: All searches are verified before execution
- **DoS protection**: Limits requests that are too fast or too large
- **Security headers**: HTTP configuration that protects against common attacks