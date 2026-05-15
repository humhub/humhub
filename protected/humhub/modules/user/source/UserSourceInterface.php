<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\source;

use humhub\modules\user\models\User;

/**
 * UserSourceInterface defines the contract for user provisioning sources.
 *
 * A UserSource is responsible for creating, updating and deleting users,
 * declaring which attributes it owns (read-only for the user), and
 * controlling user capabilities. It is orthogonal to authentication:
 * a user has one UserSource but may have multiple AuthClients.
 *
 * @since 1.19
 */
interface UserSourceInterface
{
    public const USERNAME_REQUIRE = 'require';
    public const USERNAME_AUTO_GENERATE = 'auto_generate';
    public const USERNAME_AUTO_OR_ERROR = 'auto_or_error';

    // --- Identity ---

    /**
     * Returns the unique string identifier of this source, e.g. 'local', 'ldap', 'scim_workday'.
     */
    public function getId(): string;

    /**
     * Returns a human-readable title shown in the admin UI.
     */
    public function getTitle(): string;

    // --- User Lifecycle ---

    /**
     * Creates a new HumHub user from the given attributes.
     *
     * Returns the created User on success, null on failure.
     * LocalUserSource uses Registration::register() internally — on failure
     * the caller redirects to the registration form.
     * LdapUserSource and ScimUserSource treat null as a hard failure (logged / HTTP 400).
     */
    public function createUser(array $attributes): ?User;

    /**
     * Updates an existing user with new attributes from this source.
     *
     * Called on login (if the source has an auth counterpart) or by source-specific
     * sync mechanisms. Also responsible for group and space membership updates.
     *
     * Returns false on failure; errors are logged internally.
     * A login-time failure must NOT block the login — log and proceed.
     * Callers that need field-level detail (e.g. ScimUserSource) read
     * $user->getErrors() directly after a false return.
     */
    public function updateUser(User $user, array $attributes): bool;

    /**
     * Handles user removal from the source (e.g. deleted in LDAP or via SCIM DELETE).
     * Implementation decides: disable, anonymize, or hard-delete.
     */
    public function deleteUser(User $user): bool;

    /**
     * Re-enables a previously disabled user (e.g. returned to LDAP or re-provisioned via SCIM).
     */
    public function enableUser(User $user): bool;

    // --- Attribute Ownership ---

    /**
     * Returns attribute names owned by this source (read-only in the profile UI).
     * May contain both user table fields and profile fields — consumers handle
     * the distinction via UserSourceService::getManagedAttributes().
     */
    public function getManagedAttributes(): array;

    // --- Approval ---

    /**
     * Whether newly created users from this source require admin approval.
     *
     * The optional $authClientId allows the source to decide context-dependent:
     * a source can require approval for form-based self-registration ($authClientId === null)
     * but skip it for trusted auth clients (e.g. SAML, LDAP).
     */
    public function requiresApproval(?string $authClientId = null): bool;

    // --- Auth Client Integration ---

    /**
     * Returns auth client IDs permitted for users of this source.
     * An empty array means all configured auth clients are allowed.
     */
    public function getAllowedAuthClientIds(): array;

    /**
     * Whether this source claims responsibility for creating a HumHub user
     * from the given auth client and attribute set.
     *
     * Used by {@see UserSourceCollection::findUserSourceForAuthClient()} to
     * dispatch new-user creation when several sources allow the same auth
     * client (e.g. LDAP and Local both accepting OpenID/SAML).
     *
     * The default in {@see BaseUserSource} is a plain `in_array` check on
     * {@see getAllowedAuthClientIds()}, i.e. ID-only matching with no
     * attribute inspection. Sources backed by an external directory
     * (LDAP, SCIM, …) should override to verify that the user actually
     * exists in that directory — otherwise the wrong source would adopt
     * an unrelated user and the email collision shows up on the next
     * login through the real source.
     */
    public function claimsUserCreation(string $authClientId, array $attributes): bool;

    /**
     * Whether an existing user with the same email can automatically be linked
     * to this source on first login.
     * Should return false for authoritative sources like SCIM.
     */
    public function allowEmailAutoLink(): bool;

    // --- User Capabilities ---

    /**
     * Whether users from this source may delete their own account.
     */
    public function canDeleteAccount(): bool;

    // --- Username Strategy ---

    /**
     * Defines how a username is resolved when not provided by the source.
     *
     * USERNAME_REQUIRE:        missing username → createUser() returns null → registration form shown
     * USERNAME_AUTO_GENERATE:  generated from available attributes (email, firstname, lastname),
     *                          uniqueness ensured by appending a numeric suffix (_2, _3, ...)
     * USERNAME_AUTO_OR_ERROR:  auto-generate attempted; if conflict cannot be resolved,
     *                          createUser() returns null (hard failure)
     */
    public function getUsernameStrategy(): string;
}
