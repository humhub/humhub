Contributing
============

First of all, **thank you** for contributing, **you are awesome**! :)

If you have an idea or found a bug, please [open an issue](https://github.com/cebe/markdown/issues/new) on github.

If you want to contribute code, there a few rules to follow: 

- I am following a code style that is basically [PSR-2](http://www.php-fig.org/psr/2/) but with TABS indentation (yes, I really do that ;) ).
  I am not going to nit-pick on all the details about the code style but indentation is a must. The important part is that code is readable.
  Methods should be documented using phpdoc style.

- All code must be covered by tests so if you fix a bug or add a feature, please include a test case for it. See below on how that works.

- If you add a feature it should be documented.

- Also, while creating your Pull Request on GitHub, please write a description
  which gives the context and/or explains why you are creating it.

Thank you very much!


Running the tests
-----------------

The Markdown parser classes are tested with [PHPUnit](https://phpunit.de/). For each test case there is a set of files in
the subfolders of the `/tests` folder. The result of the parser is tested with an input and an output file respectively
where the input file contains the Markdown and the output file contains the expected HTML.

You can run the tests after initializing the lib with composer(`composer install`) with the following command:

	vendor/bin/phpunit
	
To create a new test case, create a `.md` file a`.html` with the same base name in the subfolders of
the `/tests` directory. See existing files for examples.
