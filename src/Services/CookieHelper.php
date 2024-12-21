<?php

namespace Osiset\ShopifyApp\Services;

use Illuminate\Support\Facades\Config;
use Browser;

/**
 * Helper for dealing with cookie and cookie issues.
 */
class CookieHelper
{
    /**
     * The HTTP agent helper.
     *
     * @var [type]
     */

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Sets the cookie policy.
     *
     * From Chrome 80+ there is a new requirement that the SameSite
     * cookie flag be set to `none` and the cookies be marked with
     * `secure`.
     *
     * Reference: https://www.chromium.org/updates/same-site/incompatible-clients
     *
     * Enables SameSite none and Secure cookies on:
     *
     * - Chrome v67+
     * - Safari on OSX 10.14+
     * - iOS 13+
     * - UCBrowser 12.13+
     *
     * @return void
     */
    public function setCookiePolicy(): void
    {
        Config::set('session.expire_on_close', true);

        if ($this->checkSameSiteNoneCompatible()) {
            Config::set('session.secure', true);
            Config::set('session.same_site', 'none');
        }
    }

    /**
     * Checks to see if the current browser session should be
     * using the SameSite=none cookie policy.
     *
     * @return bool
     */
    public function checkSameSiteNoneCompatible(): bool
    {
        $compatible = false;
        $browser = $this->getBrowserDetails();
        $platform = $this->getPlatformDetails();

        if (Browser::browserVersionMajor() >= 67 && Browser::isChrome()) {
            $compatible = true;
        }

        if (Browser::platformVersionMajor() > 12 && Browser::isMac()) {
            $compatible = true;
        }

        if (Browser::platformVersionMajor() > 10 &&
            Browser::isMac() && Browser::isSafari() && ! Browser::isIOS()
        ) {
            $compatible = true;
        }

        if (Browser::browserVersionMajor() > 12 && Browser::isUCBrowser()) {
            $compatible = true;
        }

        return $compatible;
    }

    /**
     * Returns details about the current web browser.
     *
     * @return array
     */
    public function getBrowserDetails(): array
    {
        return $this->version(Browser::browserVersion());
    }

    /**
     * Returns details about the current operating system.
     *
     * @return array
     */
    public function getPlatformDetails(): array
    {
        return $this->version(Browser::platformVersion());
    }

    /**
     * Create a versioned array from a source.
     *
     * @param string $source The source string to version.
     *
     * @return array
     */
    protected function version(string $version): array
    {
        $pieces = explode('.', str_replace('_', '.', $version));

        return [
            'major' => $pieces[0] ?? null,
            'minor' => $pieces[1] ?? null,
            'float' => isset($pieces[0]) && isset($pieces[1]) ? (float) sprintf('%s.%s', $pieces[0], $pieces[1]) : null,
        ];
    }
}
