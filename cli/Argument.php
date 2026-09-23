<?php

require_once("inputExceptions.php");

class Argument {
    private string $name;
    private string $description;
    private string|null $short;
    private bool $valueExists;

    private string|bool $value = false;
    private string|null $nameValue;
    private bool $required = false;
    private Closure|null $check;
    private string|null $checkFailed;
    private Closure|null $transformFunction = null;

    public function __construct(string $name, string $description, string|null $short, bool $valueExists, string|null $nameValue = null, bool $required = false, Closure|null $check = null, string|null $checkFailed = null, Closure|null $transformFunction = null) {
        $this->name = $name;
        $this->description = $description;
        $this->short = $short;
        if (is_string($this->short) &&  preg_match("~..~", $this->short)) {
            throw new NotAValidArgument("shorthand is more then one char long", 1012);
        } elseif (preg_match("~\s~", $name)) {
            throw new NotAValidArgument("argument name has a whitespace in it", 1012);
        }
        $this->valueExists = $valueExists;
        if ($valueExists) {
            if (is_null($nameValue)) {
                throw new NotAValidArgument("argument with value can't have null for some arguments", 1012);
            }
            $this->nameValue = $nameValue;
            $this->required = $required;
            $this->check = $check;
            $this->transformFunction = $transformFunction;
            if (!is_null($check) && is_null($checkFailed)) {
                throw new NotAValidArgument("no message for when check failed", 1013);
            } else {
                $this->checkFailed = $checkFailed;
            }
        }
    }

    public function checkValid(): bool{
        if (is_null($this->check)) {
            return true;
        }
		return call_user_func($this->check, $this->value);
    }

    public function transform(): mixed{
		return call_user_func($this->transformFunction, $this->value);
    }

    public function getName(): string{
        return $this->name;
    }

    public function getDescription(): string{
        return $this->description;
    }

    public function getShort(): string|null{
        return $this->short;
    }

    public function getValueExists(): bool{
        return $this->valueExists;
    }

    public function getNameValue(): string|null{
        return $this->nameValue;
    }

    public function getValue(): string|bool{
        return $this->value;
    }

    public function setValue(string|bool $value): void{
        $this->value = $value;
    }

    public function getCheck(): Closure|null{
        return $this->check;
    }

    public function getCheckFailed(): string|null{
        if (is_string($this->checkFailed) && is_string($this->value)) {
            return preg_replace("~\{argument\}~i", "'".$this->value."'", $this->checkFailed);
        }
        return $this->checkFailed;
    }

    public function getRequired(): bool|null{
        return $this->required;
    }

    public function getTransformFunction(): Closure|null{
        return $this->transformFunction;
    }

    public function transformExists(): bool{
        return !is_null($this->transformFunction);
    }
}
