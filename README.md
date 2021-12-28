# JsonDecodable

Library to convert JSON to Model.

## Implement your model.

```
<?php
namespace Model;

use Traits\JsonDecodable; # +Add 

class Color
{
    use JsonDecodable; # +Add 

    private string $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    # optionnal: You can define the required keys
    public function getKeyMandatory(): array
    {
        return [
            "name"
        ];
    }
    
    # optionnal: You can map api keys 
    public function getKeyMapping(): array
    {
        return [
            "colorName" => "name", # colorName is the key receipted from API
        ];
    }
}
```

## Convert: Api => Model.

```
$color = new Color(); # create a new instance of your model.

# you can assign from StdClass
$color->setFromObject($stdClass, Color::class);
# or an array
$color->setFromArray($array, Color::class);
```