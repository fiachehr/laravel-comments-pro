# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-01

### Added
- Initial release of Laravel Comments Pro
- Nested comments with unlimited depth
- Like/Dislike reactions system
- Guest user support with fingerprinting
- Comment moderation (approve/reject/spam)
- Event system for comment lifecycle
- Polymorphic relationships for any model
- Comprehensive test suite (47 tests)
- Full documentation and examples
- Laravel 10.x and 11.x support
- PHP 8.1+ compatibility

### Features
- **Comments System**: Full-featured comment management
- **Reactions**: Like/Dislike functionality with statistics
- **Guest Support**: Anonymous user commenting
- **Moderation**: Admin approval workflow
- **Events**: CommentCreated, ReactionToggled events
- **Traits**: HasComments trait for models
- **Services**: CommentsService, ReactionService
- **Facades**: Comments, Reactions facades
- **Migrations**: Database schema management
- **Configuration**: Flexible configuration options

### Technical
- PSR-4 autoloading
- Service Provider pattern
- Enum-based status management
- Polymorphic relationships
- Event-driven architecture
- Comprehensive testing
- Clean code architecture
