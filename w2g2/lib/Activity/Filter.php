<?php

namespace OCA\w2g2\Activity;

use OCP\Activity\IFilter;
use OCP\IL10N;
use OCP\IURLGenerator;

class Filter implements IFilter {

    /** @var IL10N */
    protected $l;

    /** @var IURLGenerator */
    protected $url;

    public function __construct(IL10N $l, IURLGenerator $url) {
        $this->l = $l;
        $this->url = $url;
    }

    /**
     * @return string Lowercase a-z only identifier
     * @since 11.0.0
     */
    public function getIdentifier() {
        return 'w2g2';
    }

    /**
     * @return string A translated string
     * @since 11.0.0
     */
    public function getName() {
        return $this->l->t('File Locks');
    }

    /**
     * @return int
     * @since 11.0.0
     */
    public function getPriority() {
        return 20;
    }

    /**
     * @return string Full URL to an icon, empty string when none is given
     * @since 11.0.0
     */
    public function getIcon() {
        return $this->url->getAbsoluteURL($this->url->imagePath('core', 'actions/comment.svg'));
    }

    /**
     * @param string[] $types
     * @return string[] An array of allowed apps from which activities should be displayed
     * @since 11.0.0
     */
    public function filterTypes(array $types) {
        return $types;
    }

    /**
     * @return string[] An array of allowed apps from which activities should be displayed
     * @since 11.0.0
     */
    public function allowedApps() {
        return ['w2g2'];
    }
}
