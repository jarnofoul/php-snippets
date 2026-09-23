#!/usr/bin/env php
<?php
declare(strict_types = 1);

function showError(Exception $e): void{
	echo "Error";
	if (is_int($e->getCode())) {
		echo "(".$e->getCode().")";
	}
	if (is_string($e->getMessage())) {
		echo ": ".ucfirst($e->getMessage())."!";
	}
	echo "\n";
	exit;
}

require_once("UserInputService.php");

$ui = new UserInputService("userinputservice-test");

$ui->defineArg(new Argument(
	"user",
	"sets username",
	"u",
	true,
	"username",
));

$ui->defineArg(new Argument(
	"count",
	"sets max count",
	"c",
	true,
	"count",
	true,
	function ($count) { return ctype_digit((string)$count); },
	"max count needs to be a number not {argument}"
));

$ui->defineArg(new Argument(
	"countb",
	"sets max count",
	"b",
	true,
	"count",
	false,
	function ($count) { return ctype_digit((string)$count); },
	"max count needs to have a number",
	function ($count) { return (int)$count; },
));

$ui->defineArg(new Argument(
	"test",
	"idk",
	null,
	false,
));

$ui->defineArg(new Argument(
	"test2",
	"idk",
	null,
	false,
));


try {
	$ui->checkArgs($argv);
} catch (NotAValidArgument|InvalidInput|NotADefinedArgument|NoValueGivenThougNeeded|ValueNotValid $e) {
	showError($e);
}

if ($ui->getArg("help")) {
	$ui->showHelp(); exit;
}

var_dump($ui->getArg("user"));
var_dump($ui->getArg("count"));
var_dump($ui->getArg("countb"));
var_dump($ui->getArg("test"));
var_dump($ui->getArg("test2"));
