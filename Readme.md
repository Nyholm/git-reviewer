# Preload optimizer

Make sure your classes are perfectly configured for preloading.



Use class_exists on all:
- return type
- argument
- new class creation
- Foo::class
for non-public constructor + all constructors calls to private/protected functions.
And exclude “already known classes”