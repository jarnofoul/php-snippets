<?php

require_once("inputExceptions.php");
require_once("Argument.php");

class UserInputService {

    private string $projectName;
    private array $args = [];

    public function __construct(string $name) {
        $this->projectName = $name;
        $this->args[] = new Argument( "help", "show this help", "h", false);
    }

    public function getArg(string $asked): mixed{
        foreach ($this->args as $arg) {
            if ($arg->getName() === $asked) {
                if ($arg->transformExists()) {
                    return $arg->transform();
                }
                return $arg->getValue();
            }
        }
        throw new NotADefinedArgument("Asked for an argument that wasn't defined", 1015);
    }

    public function checkArgs(array $argv): void{
        for ($i = 1; $i < count($argv); $i++) {
            if (trim(trim($argv[$i], "-")) === "help" || trim(trim($argv[$i], "-")) === "h" ) {
                self::showHelp();
            }
        }
        for ($i = 1; $i < count($argv); $i++) {
            foreach ($this->args as $arg) {
                if ($arg->getName() === trim(trim($argv[$i], "-")) || $arg->getShort() === trim(trim($argv[$i], "-"))) {
                    if ($arg->getValueExists()) {
                        if (isset($argv[$i + 1])) {
                            $arg->setValue($argv[$i + 1]);
                            if (!$arg->checkValid()) {
                                throw new ValueNotValid($arg->getCheckFailed(),1014);
                            }
                            $i++;
                            continue 2;
                        } else {
                            throw new NoValueGivenThougNeeded("No value was given",1013);
                        }
                    } else {
                        $arg->setValue(true);
                        continue 2;
                    }
                }
            }
            throw new NotADefinedArgument($argv[$i]." not a defined argument",1012);
        }
        foreach ($this->args as $arg) {
            if ($arg->getRequired() && $arg->getValue() === false) {
                throw new RequiredVarNotGiven($arg->getName()." var not given but required", 1016);
            }
        }
    }

    public function defineArg(Argument $newArg): void{
        foreach ($this->args as $arg) {
            if ($arg->getName() === $newArg->getName()) {
                throw new ArgumentAlreadyExists($newArg->getName()." argument already exists", 1011);
            } elseif ($arg->getShort() !== null && $newArg->getShort() !== null && $arg->getShort() === $newArg->getShort()) {
                throw new ArgumentAlreadyExists($newArg->getShort()." shorthand already exists", 1011);
            }
        }
        $this->args[] = $newArg;
    }

    public function showHelp(): void{
        echo "usage: ".$this->projectName;
        foreach ($this->args as $arg) {
            echo " [";
            if (!is_null($arg->getShort())) {
                echo "-".$arg->getShort()."|";
            }
            echo "--".$arg->getName();
            if ($arg->getValueExists()) {
                echo " ";
                if (!is_null($arg->getNameValue())) {
                    echo strtoupper($arg->getNameValue());
                } else {
                    echo strtoupper($arg->getName());
                }
            }
            echo "]";
        }
        echo "\noptions:\n";
        foreach ($this->args as $arg) {
            echo "\t";
            if (!is_null($arg->getShort())) {
                echo "-".$arg->getShort().", ";
            }
            echo "--".$arg->getName()."\t";
            echo $arg->getDescription()."\n";
        }
        exit;
    }

    public function choose(array $options, string $prompt = "Choose an option"): string{
        foreach ($options as $index => $option) {
            echo ($index + 1).")".$option."\n";
        }

        $choice = (int)trim(readline(ucfirst($prompt).": "));

        if ($choice < 1 || $choice > count($options)) {
            throw new InvalidInput("Not a valid input", 1004);
        }
        return $options[$choice - 1];
    }

    public function chooseMulti(array $options, bool $exclude, string $optionName = "options"): array{
        foreach ($options as $index => $option) {
            echo ($index + 1).")".$option."\n";
        }

        if ($exclude) {
            echo ucfirst($optionName)." to exclude: (eg: '1 2 3', '1-3', '^4')\n";
        } else {
            echo ucfirst($optionName)." to include: (eg: '1 2 3', '1-3', '^4')\n";
        }

        $choice = trim(readline("=> "));

        $keys = [];

        foreach (explode(" ",$choice) as $value) {
            if (preg_match('~^\d+$~', $value)) {
                $keys[] = (int)$value - 1;
            } elseif (preg_match('~^(\d+)-(\d+)$~', $value, $matches)) {
                $keys = array_merge($keys, range(min((int)$matches[1], (int)$matches[2]) - 1, max((int)$matches[1], (int)$matches[2]) - 1));
            } elseif (preg_match('~^\^(\d+)$~', $value, $matches)) {
                $keys = array_merge($keys, range(0, (int)$matches[1] - 1));
            } elseif (preg_match('~^(\d+)\$$~', $value, $matches)) {
                $keys = array_merge($keys, range((int)$matches[1] - 1, count($options) - 1));
            }
        }

        $keys = array_unique(array_filter(
            $keys,
            fn($key) => isset($options[$key])
        ));

        if ($exclude) {
            return array_values(array_diff_key($options, array_flip($keys)));
        }

        return array_values(array_intersect_key($options, array_flip($keys)));
    }

    public function getInput(string $varname, bool $pass = false): string{
        echo "{$varname}: ";
        if (!$pass) {
            return trim(fgets(STDIN));
        }
        system('stty -echo');
        $input = fgets(STDIN);
        system('stty echo');
        echo PHP_EOL;
        return trim($input);
    }

    public function getProjectName(): string{
        return $this->projectName;
    }
}
