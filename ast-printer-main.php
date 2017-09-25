<?php
require_once('ast-printer.php');

$expression = new Binary(
	new Unary(
		new Token(TOK_MINUS, "-", null, 1),
		new Literal(123)
	),
	new Token(TOK_STAR, "*", null, 1),
	new Grouping(
		new Literal(45.67)
	)
);

echo (new AstPrinter())->print($expression)."\n";
