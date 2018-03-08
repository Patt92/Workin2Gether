<?php

namespace OCA\w2g2;

class User
{
    /**
     * Get a user's display name given his/hers username or the username if the display name is not set.
     *
     * @param $username
     * @return mixed
     */
    public static function getDisplayName($username)
    {
        $usersMatchingUsername = Database::getUserByUsername($username);

        // check if the user is stored within the 'users' table and not in the 'accounts' table (ex: ldap)
        if (
            count($usersMatchingUsername) > 0 &&
            array_key_exists("displayname", $usersMatchingUsername[0]) &&
            $usersMatchingUsername[0]["displayname"]
        ) {
            return $usersMatchingUsername[0]["displayname"];
        }

        $ldapUsersMatchingUsername = Database::getUserByLDAPUsername($username);

        if (count($ldapUsersMatchingUsername) > 0) {
            $ldapUser = $ldapUsersMatchingUsername[0];
            $ldapUserData = $ldapUser['data'];

            $parsedJsonResults = json_decode($ldapUserData, true);

            // parse all fields from the data, find the attribute 'displayname'
            // and take the data from the value subattribute
            foreach ($parsedJsonResults as $attribute => $data) {
                if ($attribute === "displayname" && $data["value"]) {
                    return $data["value"];
                }
            }
        }

        return $username;
    }

    public static function getCurrentUserName()
    {
        return \OCP\User::getUser();
    }

    public static function getCurrentUserDisplayName()
    {
        return \OCP\User::getDisplayName();
    }
}
