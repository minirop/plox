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
