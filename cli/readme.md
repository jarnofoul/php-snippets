# README

This is a readme to the **UserInputService**, though only the argmuent for a cli program at launch. There are some other methods in this Service, which ask the user for input during runtime:
- `getInput(string $varname, bool $password = false): string`
- `chooseMulti(array $options, bool $exclude, string $optionName = "options"): array`
- `choose(array $options, string $prompt = "Choose an option"): string`

For more info on these, read the *UserInputService.php* file


# Init Service

```php
$service = new UserInputService($projectname);
```

The `$projectname` var is a required var that's seen when the `--help` page is generated.
It can also be queried with `$service->getProjectName()`

# Define arguments

## Defining the simpelest argument: a flag

A flag is an argument where the resulting value is either `true` or `false`, if the flag is set it returns `true` else  `false`.

```php
$service->defineArg(new Argument(
	"argumentName",         // argument name
	"sets argument",        // description
	"a",                    // shorthand
	false,                  // has value
));
```

The values in the `Argument` constructor:
1. `argument name` -> this is literly the argumen name:
    - This argument gets called with it eg. `--argumentName`
    - When there's a space in `$argumentName` there's a `NotAValidArgumnt` exception thrown
2. `description` -> this is the discription of the argument, and gets shown in the `--help`
3. `shorthand` -> a shorhand for the `argument name`
    - The argument gets called with it eg. `-a`
    - If the shorhand is more then one `char` long there's a `NotAValidArgumnt` exception thrown
    - If no shorthand is needed set this to `null`
4. `has value` -> this says if the argument has a value associated with it, if not said then it's treated like a flag and are the results only `true` or `false`
    - If this is `false` nomore arguments are needed in the constructor

>
>   If a new argument is defined with either the same shorthand or argumentName a `ArgumentAlreadyExists` exception is thorwn
>

## Defining a simple argument with a value associated

An argument with a simple value associated is called with `--argumentName 'VALUE'` or `-a 'VALUE'`, there's no support for `=`.

```php
$service->defineArg(new Argument(
	"argumentName",     // argument name
	"sets argument",    // description
	null,               // shorthand
	true,               // has value
	"argument",         // value name
	true|false,         // required
));
```

The most important part, which makes this a argument with a value is the `has value` field in the constructor, which is now said to `true`.
If this field is said to true, the following argument has to be said `value name`, this doesn't have any meaning except in the `--help` view.

>   Also of some note is the fact that the shorthand here is said to `null` which means it can only be called with `--argumentName 'VALUE'`.

The `required` field in the constructor doesn't have to be set and defaults to `false`, when said to true it throws an `RequiredVarNotGiven` exception when this argument is not called, except when `--help` is called.
The `$service` also thorws an `NoValueGivenThougNeeded` exception if you call a argument that has a `has value` field set to true, without a value given. **There's no way to set a default value.**

## Defining and checking an argument with a value

With 2 extra arguments in the constructor we can check if a value is a *valid* value for that argument.

```php
$service->defineArg(new Argument(
	"argument",         // argument name
	"sets argument",    // description
	null,               // shorthand
	true,               // has value
	"argument",         // value name
	true|false,         // required
	Closure: bool,      // checkValid
    "argument is not valid {argument}" // check failed message
));
```

Here the `checkValid` field asks for `Closure`
for example:
```php
function ($count) { return ctype_digit((string)$count); },
```
This `Clouser` takes the `$count` variable as input and uses that to check if the *value* given with `--argument 'VALUE'` is valid.

With this you can check for example if it's a string that passes certain checks. One argument has to be given as input, but if the user wants to check somthing else than this is also fine.

The `checkValid` function has to return a *truethy* value to pass.
If the `checkValid` *doesn't pass* a `ValueNotValid` exception is thorown with as message the required `check failed message`

> In the `check failed message` if you use `{argument}` in the string the argument give will apeare in quotes

## Getting the value

Using: `$service->getArg("argument");` after defining an argument with `$service->defineArg` you can get the output from that argument.

There are a couple of senarios:
    - Wheter if was a simple **flag** or with a value, if you didn't call it it'll return `false`
    - If it was a simple **flag** argmunt and you called it it'll return `true`
    - If there was a value and you called it, and there either was no `checkValid` or it passed it you get a string of that value

### Changing the output

You can change the output that you get with `getArg()` by setting a `transform` function

```php
$service->defineArg(new Argument(
	"argument",         // argument name
	"sets argument",    // description
	null,               // shorthand
	true,               // has value
	"argument",         // value name
	true|false,         // required
	Closure: bool,      // checkValid
    "argument is not valid {argument}" // check failed message
	Closure: mixed,     // transform
));
```

An exaple for a transform function would be:
`function ($count) { return (int)$count; }`
Using the same logic as `checkValid`, but this doesn't need to return a `bool` as long as it isn't `void` it'll work.

In the example given, it returns an int instead of a string.

# Exceptions

A list of all the exceptions that can be thrown and why:

- `NotADefinedArgument` user put a argument that wasn't defined or dev asked a argument with `getArg()` that isn't defined
- `ValueNotValid` value given to argument doesn't pass the `checkValid` function
- `NoValueGivenThougNeeded` Argument which needs an value is called without an value
- `RequiredVarNotGiven` a argument that needs to be called isn't called
- `ArgumentAlreadyExists` a argument is defined twice, either same name or same shorthand
- `NotAValidArgumnt` called for a multitude of reasons, most of the time reason is in message, is called when constructor for an argument doesn't pass
