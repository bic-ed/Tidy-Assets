# Changelog
## [Unreleased] - 2023-01-03

### Fixed
* Rare JavaScript errors like _(intermediate value)(...) is not a function_ were resolved by adding a semicolon after each block of internal code, before consolidating.

## [v1.1] - 2023-01-03

### Added
* Preserve formatting of `<pre>` tags in html output
* An option to remove jQuery Migrate from the site front-end

### Changed
* Implemented `preg_split` to detect end-of-line
* CDN links updated to jQuery 3.6.3 but for Google (3.6.1) and Microsoft (3.6.0)

### Removed
* The latest version of jQuery for self hosting is no longer included, as it is now provided by Zenphoto (since ZP v1.6)


[Unreleased]: https://github.com/bic-ed/Tidy-Assets/compare/v1.1..master
[v1.1]: https://github.com/bic-ed/Tidy-Assets/compare/v1.0.0...v1.1
