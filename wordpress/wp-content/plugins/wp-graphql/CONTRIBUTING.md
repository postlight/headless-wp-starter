## Contribute to WPGraphQL

WPGraphQL welcomes community contributions, bug reports and other constructive feedback.

When contributing please ensure you follow the guidelines below so that we can keep on top of things.

## Getting Started

* __Do not report potential security vulnerabilities here. Email them privately to our security team at 
[info@wpgraphql.com](mailto:info@wpgraphql.com)__
* Before submitting a ticket, please be sure to replicate the behavior with no other plugins active and on a base theme like Twenty Seventeen.
* Submit a ticket for your issue, assuming one does not already exist.
  * Raise it on our [Issue Tracker](https://github.com/wp-graphql/wp-graphql/issues)
  * Clearly describe the issue including steps to reproduce the bug.
  * Make sure you fill in the earliest version that you know has the issue as well as the version of WordPress you're using.

## Making Changes

* Fork the repository on GitHub
* Make the changes to your forked repository
  * Ensure you stick to the [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards)
* When committing, reference your issue (if present) and include a note about the fix
* If possible, and if applicable, please also add/update unit tests for your changes
* Push the changes to your fork and submit a pull request to the 'develop' branch of this repository

## Code Documentation

* We strive for full doc coverage and follow the standards set by phpDoc
* Please make sure that every function is documented so that when we update our API Documentation things don't go awry!
	* If you're adding/editing a function in a class, make sure to add `@access {private|public|protected}`
* Finally, please use tabs and not spaces.

At this point you're waiting on us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary.

> **NOTE:** This CONTRIBUTING.md file was forked from [Easy Digital Downloads](https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/CONTRIBUTING.md)