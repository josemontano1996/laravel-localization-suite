### [← Back to Main Documentation](../README.md)

# Blade Directives

The Laravel Localization Suite provides a collection of helpful Blade directives to make building multilingual views easier.

## Navigation & Translation

### `@route`
Generates a localized route URL. Automatically injects the current locale into the route generation.

```blade
<a href="@route('posts.show', ['id' => 1])">View Post</a>
```

### `@t`
Translates a given string key using the current locale (wrapper around `__()`).

```blade
<h1>@t('messages.welcome')</h1>
<p>@t('messages.hello', ['name' => 'John'])</p>
```

### `@tchoice`
Translates a pluralized string based on a count (wrapper around `trans_choice()`).

```blade
<p>@tchoice('messages.apples', 5)</p>
```

---

## State & Conditionals

### `@locale`
Outputs the current active locale.

```blade
<html lang="@locale">
```

### `@localeIs`
Conditional block that executes if the current locale matches the given code.

```blade
@localeIs('en')
    <p>This is only visible in English.</p>
@endlocaleIs
```

### `@locales`
Loops through all supported locales configured in your application.

```blade
<ul>
    @locales($code)
        <li>
            <a href="{{ URL::withLocale($code) }}">{{ strtoupper($code) }}</a>
        </li>
    @endlocales
</ul>
```

---

## Formatting

### `@number`
Formats a number according to the current locale using `NumberFormatter::DECIMAL`.

```blade
<span>@number(1234.56)</span>
{{-- "1,234.56" in en, "1.234,56" in es --}}
```

### `@currency`
Formats a number as currency according to the current locale using `NumberFormatter::CURRENCY`.
Parameters: `value`, `currency_code` (default: 'USD'), `decimals` (optional).

```blade
<span>@currency(1234.56, 'EUR')</span>
{{-- "€1,234.56" in en, "1.234,56 €" in es --}}
```

### `@percent`
Formats a number as a percentage according to the current locale using `NumberFormatter::PERCENT`.

```blade
<span>@percent(0.85)</span>
{{-- "85%" in en, "85 %" in es --}}
```

---

## Dates

The date directives use `CarbonImmutable` to format dates according to the current locale.

### `@date`
Formats a date using `isoFormat()`. Default format is 'LL' (e.g., "September 4, 1986").

```blade
<time>@date($user->created_at)</time>
<time>@date('now', 'MMMM YYYY')</time> {{-- Custom format --}}
```

### `@datetime`
Formats a date and time using `isoFormat()`. Default format is 'LLL' (e.g., "September 4, 1986 8:30 PM").

```blade
<time>@datetime($user->created_at)</time>
```

### [← Back to Main Documentation](../README.md)
