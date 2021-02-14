<?php

namespace WPStaging\Bootstrap\V1;

if (!class_exists(WpstgRequirements::class)) {
    abstract class WpstgRequirements
    {
        protected $notificationTitle   = '';
        protected $notificationMessage = '';
        protected $entryPoint;

        public function __construct($entryPoint)
        {
            $this->entryPoint = $entryPoint;
        }

        abstract public function checkRequirements();

        /**
         * Checks if the current WordPress version is above
         * the minimum required.
         */
        protected function meetsMinimumWordPressVersion()
        {
            $minimumWordPressVersion = '4.0';
            $currentWordPressVersion = get_bloginfo('version');

            $meets = version_compare($currentWordPressVersion, $minimumWordPressVersion, '>=');

            if (!$meets) {
                $this->notificationMessage = __(sprintf('WPSTAGING requires at least WordPress %s to run. You have WordPress %s.', $minimumWordPressVersion, $currentWordPressVersion), 'wp-staging');
                add_action('admin_notices', [$this, '_displayWarning']);

                throw new \RuntimeException($this->notificationMessage);
            }
        }

        /**
         * Usage:
         * $this->notificationMessage = 'Foo';
         * add_action('admin_notices', [$this, '_displayWarning']);
         */
        public function _displayWarning()
        {
            $title   = esc_html($this->notificationTitle ?: __('WP STAGING', 'wp-staging'));
            $message = wp_kses_post($this->notificationMessage);

            echo <<<MESSAGE
<div class="notice-warning notice is-dismissible">
    <p style="font-weight: bold;">$title</p>
    <p>$message</p>
</div>
MESSAGE;

            // Cleanup the state.
            $this->notificationTitle   = '';
            $this->notificationMessage = '';
        }
    }
}
