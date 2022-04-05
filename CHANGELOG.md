# PHP-IPAddress

A set of utility classes for working with IP addresses in PHP.
Supports both IPv4 and IPv6 schemes.

Fork to support PHP 8.1

## Ver 2.0.0

PHP 8.1 required


## New features
is_same_version is now public
## Backwards compability breaking changes:

#### get_block_in_smallest()
 Now throws instead of returning [null, null] when no smallest block is found

#### get_octet
Now throws InvalidArgumentException instead of returning null when no smallest block is found
