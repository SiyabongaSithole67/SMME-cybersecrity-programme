<?php

// Include the User model and Database connection class
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/DatabaseUtil.php";

class UserController {

    // Private property to hold the PDO database connection
    private $db;

    // Constructor runs automatically when a new UserController object is created
    public function __construct() {
        // Create a new Database object and connect to the database
        // Store the PDO connection in $this->db for use in all methods

        $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Create a new user in the system
     * 
     * @param UserModel $currentUser The currently logged-in user performing the action
     * @param array $userData Array containing new user data: name, email, password, role_id, organisation_id
     * 
     * @return bool Returns true if insertion was successful, false otherwise
     * 
     * Access control:
     * - SystemAdmin (role_id = 1) → can create any user
     * - OrgAdmin (role_id = 2) → can only create employees (role_id = 3) in their own organisation
     * - Employees (role_id = 3) → cannot create users
     */

    public function createUser($currentUser, array $userData) { //create a user

        // Check the role of the current user
        if ($currentUser->getRoleId() == 1) {
            // SystemAdmin → full power, allowed to create any user
        } elseif ($currentUser->getRoleId() == 2) {
            // OrgAdmin → can only create employees in their own organisation
            if ($userData['role_id'] != 3 || $userData['organisation_id'] != $currentUser->getOrganisationId()) {
                die("Access denied: OrgAdmin can only create employees in their organisation");
            }
        } else {
            // Any other role (employees) cannot create users
            die("Access denied!");
        }

        // Prepare SQL statement to insert a new user into the 'users' table
        // Using prepared statements prevents SQL injection attacks
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role_id, organisation_id) VALUES (?, ?, ?, ?, ?)"
        );

        // Execute the SQL statement with the actual user data
        // The array values correspond to the placeholders (?) in the prepared statement
        return $stmt->execute([
            $userData['name'],
            $userData['email'],
            $userData['password'],
            $userData['role_id'],
            $userData['organisation_id']
        ]);
    }

    /**
     * List users visible to the current user
     * 
     * @param UserModel $currentUser The currently logged-in user
     * 
     * @return array Returns an array of users as associative arrays
     * 
     * Access control:
     * - SystemAdmin → sees all users
     * - OrgAdmin → sees users only in their organisation
     * - Employee → sees only their own account
     */
    public function listUsers($currentUser) { //get or list all users

        // SystemAdmin → retrieve all users
        if ($currentUser->getRoleId() == 1) {
            $stmt = $this->db->query("SELECT * FROM users");

        // OrgAdmin → retrieve users in the same organisation
        } elseif ($currentUser->getRoleId() == 2) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE organisation_id=?");
            $stmt->execute([$currentUser->getOrganisationId()]);

        // Employee → retrieve only their own record
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
            $stmt->execute([$currentUser->getId()]);
        }

        // Fetch all results as an array of associative arrays and return
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // method to select one user

/**
 * 
 * C-Create
 * R-Read (viewing details)
 * U-Update (Updating data)
 * D-Delete 
 */





}




