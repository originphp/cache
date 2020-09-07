# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.5] - 2020-09-07

### Fixed

- Fixed FileEngine issue in retrieving values when serialization is disabled
- Fixed FileEngine issue when clearing cache with multiple cache configurations using subdirectories

## [1.2.4] - 2020-07-23

### Fixed

- Fixed error handling for incorrect password due to recent changes in extension

## [1.2.3] - 2020-07-16

### Added

- Added FileEngine `mode` for newly created cache files

### Fixed

- Fixed issue with changing duration using strings from individual engines which did not convert to integer

## [1.2.2] - 2020-06-01

### Changed

- Adjusted travis php 7.4 configuration

## [1.2.0] - 2019-10-31

### Changed

- Enabled FileEngine increment / decrement
- FileEngine will create path if it does not exist

## [1.1.0] - 2019-10-30

### Changed

- Changed Redis engine to serialize data and work in a unified way with other engines.

## [1.0.2] - 2019-10-29

### Fixed

- Fixed return types for increment/decrement

## [1.0.1] - 2019-10-14

### Fixed

- Bumped configurable version to 1.x

## [1.0.0] - 2019-10-14

This component has been decoupled from the [OriginPHP framework](https://www.originphp.com/).