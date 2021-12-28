<?php


namespace Traits;


use ReflectionClass;
use ReflectionProperty;
use stdClass;

trait JsonDecodable
{

    public function getKeyMapping(): array
    {
        return [];
    }

    public function getKeyMandatory(): array
    {
        return [];
    }

    function setFromObject(stdClass $std, $obj): bool
    {
        return $this->setFromArray((array)$std, $obj);
    }

    function setFromArray(array $array, $obj): bool
    {
        try {
            foreach ($array as $key => $value) {
                $objKey = $key;

                /*
                 * Update keyName if needed
                 */
                if (array_key_exists($key, $this->getKeyMapping())) {
                    $objKey = $this->getKeyMapping()[$key];
                }

                if (!property_exists($obj, $objKey)) {
                    continue;
                }

                if($value === null) {
                    continue;
                }

                /*
                * Check is var is a
                */
                $rp = new ReflectionProperty($obj, $objKey);
                $type = $rp->getType();

                if ($type != null && $type->getName() != null && ctype_upper(substr($type->getName(), 0, 1))) {
                    $name = $type->getName();
                    $reflect = new ReflectionClass($name);
                    $reflectTraitsKeys = array_keys($reflect->getTraits());
                    $traitIsUsed = in_array(
                        JsonDecodable::class,
                        $reflectTraitsKeys
                    );
                    if ($traitIsUsed) {
                        $this->$objKey = new $name();
                        $this->$objKey->setFromObject($value, $name);
                        continue;
                    }
                }

                /*
                * try to use the setter method
                */
                $setMethod = "set" . ucfirst($objKey);
                $methodExist = method_exists($obj, $setMethod);
                if ($methodExist) {
                    $this->$setMethod($value);
                    continue;
                }

                /*
                 * Else we directly assign the value to the variable
                 */
                $this->$objKey = $value;
            }
            return true;
        } catch (\Error | \ReflectionException $e) {
            return false;
        }
    }

    /**
     * Return the name of the undefined key
     * @return String|null
     */
    function checkMandatory(): ?string
    {
        foreach ($this->getKeyMandatory() as $key) {
            if (!isset($this->$key) || (is_string($this->$key) && empty($this->$key))) {
                return $key;
            }
        }
        return null;
    }
}