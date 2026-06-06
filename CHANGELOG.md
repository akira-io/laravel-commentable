# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.3.0](https://github.com/akira-io/laravel-commentable/compare/v0.2.1...v0.3.0) (2026-06-06)

### Bug Fixes

- **package:** Authorize forced comment deletion ([79b6b67](https://github.com/akira-io/laravel-commentable/commit/79b6b67cc94a4a73a0f801189a9835b4a597aa81))
- **package:** Satisfy event constructor annotations ([b7e2945](https://github.com/akira-io/laravel-commentable/commit/b7e2945c6bbb3633263aea65545d0bdaadf6fd7b))


### Features

- **package:** Add soft deletes and revisions ([555bbe8](https://github.com/akira-io/laravel-commentable/commit/555bbe876f807796d8b9ff25e510622c505ab06a))
- **package:** Add moderation queue APIs ([7f333dc](https://github.com/akira-io/laravel-commentable/commit/7f333dcaeccf6bde354e0ae6146787eb93e7caf8))
- **package:** Add comment lifecycle gates ([c5c9bc9](https://github.com/akira-io/laravel-commentable/commit/c5c9bc9c60b21bf4ce32c8a9f38bf830589390f4))

## [0.2.1](https://github.com/akira-io/laravel-commentable/compare/v0.2.0...v0.2.1) (2026-06-03)

### Bug Fixes

- **package:** Resolve reaction relationships ([22f61ee](https://github.com/akira-io/laravel-commentable/commit/22f61eee3bef2e9f7892e46519b0998993930fd8))
- **package:** Honor commentable model configuration ([2e078e7](https://github.com/akira-io/laravel-commentable/commit/2e078e7084160b1efd99874bbf80fb96254e1287))
- **package:** Resolve reaction relationship conflicts ([45840fd](https://github.com/akira-io/laravel-commentable/commit/45840fd3be1757bf83d60454deade3a6ac1ba6d4))

## [0.2.0](https://github.com/akira-io/laravel-commentable/compare/0.1.0...v0.2.0) (2026-06-03)

### Features

- **package:** Support laravel 13 and bun ([46f28e3](https://github.com/akira-io/laravel-commentable/commit/46f28e3a3283855318bb8a9d3908104e03e6bb3d))

## [0.1.0](https://github.com/akira-io/laravel-commentable/compare/...0.1.0) (2025-12-23)

### Code Refactoring

- Update package details and improve code structure ([b6c89b1](https://github.com/akira-io/laravel-commentable/commit/b6c89b1cfc03b695517dc1dba89efa0b3b70bcf1))
- Improve type hinting and code structure in comment-related classes ([1effb93](https://github.com/akira-io/laravel-commentable/commit/1effb9311a7ce63652458360395d9ea7cf971831))
- Simplify Comment and Commentable classes by removing unused methods ([6aeaa3d](https://github.com/akira-io/laravel-commentable/commit/6aeaa3d31ea08a51240784f07d41ca2d203fbdd2))
- Update Commenter methods to use CommentContract type hinting ([0033c90](https://github.com/akira-io/laravel-commentable/commit/0033c901b48366dd6008921ee14c6ffad36fbfa4))


### Features

- Implement comment and reply functionality with associated models and traits ([81c1455](https://github.com/akira-io/laravel-commentable/commit/81c14552837a16984e5e824edc10a936612a04c7))
- Introduce CommentContract interface and update Commenter and Message classes to implement it ([955afbb](https://github.com/akira-io/laravel-commentable/commit/955afbba0275d06fa75c9c2eba3cee4107611535))

