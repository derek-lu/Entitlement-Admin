## Changelog

### Updated to use empty() on the $guid_array instead of the $guid, because of empty() on a string was returning true, regardless if it was actually empty or not.

See the output below for when the sign in is unsuccessful and $guid is empty:

```php
$guid = ""

empty($guid)    = "true"
if($guid)       = "false"
isset($guid)    = "false"
strlen($guid)   = "0"
count($guid)    = "0"
```

See the output below for when the sign in is successful and $guid value is set:

```php
$guid = "c5af08b5-5adf-52fb-aae1-464490637ed9"

empty($guid)    = "true"
if($guid)       = "false"
isset($guid)    = "true"
strlen($guid)   = "36"
count($guid)    = "0"
```