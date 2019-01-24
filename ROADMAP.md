These are main plans that are additional to [open issues](https://github.com/yiisoft/yii-core/milestones/3.0.0).

# 3.0

## Re-structure

- [x] Split framework into packages with separate releases.
- [ ] Finish [new application template].

## Documentation

- [ ] Prepare [upgrading instructions](https://github.com/yiisoft/yii-core/blob/master/UPGRADE.md).
- [ ] Separate changes description from upgrading instructions?
- [ ] Update guide and docs to reflect DI container changes.

## Build & Release

- [ ] Mark conflicting all packages that require it. See [#16](https://github.com/yiisoft/yii-core/pull/16)
- [ ] Make sure all official extensions have releases for 3.0.0.
- [ ] Fix tests in [all packages].
- [ ] Setup general package maintenance (php-cs-fixer, phpstan, ...) with hidev.
- [ ] Setup automatic builds (travis, scrutinizer, ...) for [all packages].

## Cleanup

- [ ] Remove all `@since` annotations
- [ ] See if more PHP 7.1 features could be used and more compatibility hacks removed.
- [ ] [Cleanup `ErrorHandler`](https://github.com/yiisoft/yii2/issues/14348).
- [ ] [Split `IdentityInterface`](https://github.com/yiisoft/yii2/issues/13825).
- [ ] Use dependency injection properly. Make sure container is not passed around and static variables aren't used.
- [ ] Decouple components as much as possible.
- [ ] Use type-hinting everywhere.
- [ ] Increase type strictness.

## Architecture

- [ ] Revise application lifecycle.
- [ ] [PSR-15 compatible middleware](https://github.com/yiisoft/yii2/issues/15438).
- [ ] Prefer throwing exceptions to fixing input.
- [ ] Make sure error handler catches fatals and is using response.

## i18n

- [ ] Use kebab-case instead of snake (`-` instead of `_`) for view files, message files etc. [See #8057](https://github.com/yiisoft/yii2/pull/8057)

## Extensions

- [ ] Remove all widgets that doing things that could be done simpler via plain HTML.

## Less Dependencies

- [ ] [Re-write Gii JavaScript not to use jQuery](https://github.com/yiisoft/yii2-gii/issues/282).
- [ ] [Re-write Debug JavaScript not to use jQuery](https://github.com/yiisoft/yii2-debug/issues/246).

# 4.0 (2018)

- [ ] Announce LTS.
- [ ] PHP 7 strict scalar types everywhere.
- [ ] Decouple routing from controllers and modules. Allow specifying any class method as a callback for a matching route.
- [ ] Merge `components` and DI container configs.
- [ ] Try to eliminate `Object` and `Component` turning these into traits. Could extract AccessorTrait, EventTrait etc. Alternatively we can drop accessors. Will get [PSR-2](https://github.com/yiisoft/yii2/issues/11956) and stricter interfaces in exchange additionally to possibility to get more performance.
- [ ] When triggering events, pass data as a separate argument instead of a part of event object (commonly referred to as inconvenient).
- [ ] Move methods from Yii class into helpers. For example, `Yii::getAlias()` could be `FileHelper::getAlias()`.
- [ ] Use HTML-5 data attributes to specify validation rules + global validation script that doesn't require additional config.
- [ ] Implement `change()` for migrations.

[all packages]:                 https://github.com/yiisoft/docs/blob/master/packages.md
[new application template]:     https://github.com/yiisoft/yii-project-template
