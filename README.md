PLOX
====

Plox is an inplementation of the [Lox language](http://www.craftinginterpreters.com/the-lox-language.html) in PHP.

How to try it
-------------

    # run a Lox script
    $ php plox.php hello.lox
    # or after 'chmod +x plox'
    $ ./plox hello.lox

If you want to try the Lox syntax, you can also launch the interpreter without a filename to have a REPL prompt (you have to write all the code on one line):

    $ ./plox
    > var test = 3; print test;
    3
    >

Improvements
------------

I've added a few things that the book does not cover.

### Constants collapsing

If a binary operator is used with two constants, then a new constant node is created instead of the binary operator node.

`1 + 1` creates `LiteralExpr(2)` instead of `BinaryExpr(LiteralExpr(1), '+', LiteralExpr(1))`.

### Warnings

I've added a couple of warnings about variables being declared to soon or being unused.

```
var a = 3;
var b = 4;
{
	print(a); // WARNING: variable 'a' declared on line 1 is only used in an inner scope.
}
// WARNING: variable 'b' declared on line 2 is never used.
```

TODO: add 'read uninitialised variable' and 'variable never read'
