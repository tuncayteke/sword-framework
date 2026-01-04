<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * English Language File - Core
 */

return [
    // General
    'welcome' => 'Welcome',
    'hello' => 'Hello',
    'goodbye' => 'Goodbye',
    'yes' => 'Yes',
    'no' => 'No',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'view' => 'View',
    'back' => 'Back',
    'next' => 'Next',
    'loading' => 'Loading...',

    // User
    'user' => [
        'not_found' => 'User not found',
        'created' => 'User created',
        'updated' => 'User updated',
        'deleted' => 'User deleted',
        'login_required' => 'Login required'
    ],

    // Validation
    'validation' => [
        'required' => ':field field is required',
        'email' => ':field must be a valid email',
        'min' => ':field must be at least :min characters',
        'max' => ':field must be at most :max characters',
        'unique' => ':field is already taken'
    ],

    // Error messages
    'error' => [
        'general' => 'An error occurred',
        'not_found' => 'Page not found',
        'unauthorized' => 'Unauthorized access',
        'forbidden' => 'Access denied',
        'server_error' => 'Server error'
    ],

    // Upload messages
    'upload' => [
        'directory_create_failed' => 'Upload directory could not be created: :path',
        'file_upload_failed' => 'File could not be uploaded: :name',
        'invalid_upload' => 'Invalid file upload operation',
        'file_too_large' => 'File size is too large. Maximum size: :max MB',
        'unsupported_type' => 'File type not supported: :type',
        'ini_size_exceeded' => 'File size exceeds the maximum size allowed in PHP configuration',
        'form_size_exceeded' => 'File size exceeds the maximum size specified in the form',
        'partial_upload' => 'File was only partially uploaded',
        'no_file' => 'No file was uploaded',
        'no_tmp_dir' => 'Missing temporary folder',
        'cant_write' => 'Failed to write file to disk',
        'extension_stopped' => 'A PHP extension stopped the file upload',
        'unknown_error' => 'An unknown error occurred'
    ],

    // Model messages
    'model' => [
        'admin_cannot_delete' => 'Admin users cannot be deleted',
        'username_taken' => 'This username is already taken',
        'user_created' => 'User created: ID=:id, Username=:username',
        'user_updated' => 'User updated: ID=:id',
        'user_deleted' => 'User deleted: ID=:id'
    ],

    // Form messages
    'form' => [
        'all_fields_required' => 'Please fill in all fields',
        'message_sent' => 'Your message has been sent successfully',
        'message_failed' => 'Message could not be sent'
    ],

    // Success messages
    'success' => [
        'saved' => 'Successfully saved',
        'updated' => 'Successfully updated',
        'deleted' => 'Successfully deleted',
        'sent' => 'Successfully sent'
    ]
];
