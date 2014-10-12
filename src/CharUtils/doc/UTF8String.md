# UTF8String

This object provides proper iteration, measuring and other utility functions with UTF-8 encoded strings. With this library you no longer have to worry about Unicode messing things up and you don't have to fuss with things that should be simple, like getting the ordinal (numeric) value of any character.

### __construct($str)

- **str** This should be a valid UTF-8 (or ASCII) encoded string.

### UTF8String#ord($char)

Get the ordinal value of a valid UTF8 character.

This method can be called statically (e.g. `UTF8String::ord('Ȉ')`) or as a method of an instantiated object. As an object member you may pass either a character or an offset from the string represented by the object. As a static method you may only pass a character.


## Access and Iteration

`UTF8String` objects can be accessed like an array, similar to PHP strings, except that you reference character offsets instead of byte offsets.

There are two types of supported iteration: Array-like iteration with a `for` loop and iterator based to use with `foreach`.

#### Array access and iteration

```php
$str = new UTF8String("strȈng");

echo $str[3] . " " . $str[4] . "\n";

for($i = count($str) - 1; $i >= 0; $i--) {
    echo $str[$i];
}
```

The above will output:
```
Ȉ n
gnȈrts
```

#### Iterator

```php
$str = new UTF8String("strȈng");
foreach($str as $ch) {
    echo $ch . ' ';
}

echo "\n" . implode('-', iterator_to_array($str));
```

The above will output:
```
s t r Ȉ n g 
s-t-r-Ȉ-n-g
```