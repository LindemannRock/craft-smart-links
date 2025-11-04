<?php
/**
 * Smart Links plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinks\integrations;

use lindemannrock\smartlinks\SmartLinks;

/**
 * Redirect Manager Integration
 *
 * NOTE: This integration is different from SEOmatic.
 * - SEOmatic: Pushes analytics EVENTS to external service (via pushEvent)
 * - Redirect Manager: Creates REDIRECTS via trait methods (direct API calls)
 *
 * This class exists for:
 * - Status checking (isAvailable, isEnabled)
 * - UI display (getStatus)
 * - Architecture consistency
 *
 * Actual redirect creation happens in SmartLinksService via RedirectHandlingTrait:
 * - handleSlugChange() -> calls trait's handleUndoRedirect() and createRedirectRule()
 * - handleDeletedSmartLink() -> calls trait's createRedirectRule()
 *
 * @since 1.1.0
 */
class RedirectManagerIntegration extends BaseIntegration
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->handle = 'redirect-manager';
        $this->name = 'Redirect Manager';
    }

    /**
     * Check if Redirect Manager plugin is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isPluginInstalled('redirect-manager');
    }

    /**
     * Push event to Redirect Manager
     *
     * NOTE: This method is not used for Redirect Manager integration.
     * Redirect creation happens directly in SmartLinksService via RedirectHandlingTrait.
     * This is a no-op to satisfy the IntegrationInterface contract.
     *
     * @param string $eventType Event type (not applicable)
     * @param array $data Event data (not applicable)
     * @return bool Always returns true (no-op)
     */
    public function pushEvent(string $eventType, array $data): bool
    {
        // Redirect Manager integration doesn't use event pushing
        // Redirects are created via trait methods in the service layer:
        // - SmartLinksService::handleSlugChange() -> trait's createRedirectRule()
        // - SmartLinksService::handleDeletedSmartLink() -> trait's createRedirectRule()
        return true;
    }

    /**
     * Get Redirect Manager integration status
     *
     * @return array
     */
    public function getStatus(): array
    {
        $settings = SmartLinks::getInstance()->getSettings();
        $redirectManagerEvents = $settings->redirectManagerEvents ?? [];

        return [
            'name' => $this->getName(),
            'handle' => $this->getHandle(),
            'available' => $this->isAvailable(),
            'enabled' => $this->isEnabled(),
            'events' => $redirectManagerEvents,
            'description' => 'Creates permanent redirects when smart link slugs change or links are deleted',
        ];
    }

    /**
     * Validate event data
     *
     * NOTE: Not used for Redirect Manager integration.
     * This is a no-op to satisfy the IntegrationInterface contract.
     *
     * @param string $eventType
     * @param array $data
     * @return bool Always returns true
     */
    public function validateEventData(string $eventType, array $data): bool
    {
        // No event validation needed for Redirect Manager
        return true;
    }
}
