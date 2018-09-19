# Laravel Storage Connect

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stechstudio/laravel-storage-connect.svg?style=flat-square)](https://packagist.org/packages/stechstudio/laravel-storage-connect)
[![Build Status](https://travis-ci.org/stechstudio/laravel-storage-connect.svg?branch=master)](https://travis-ci.org/stechstudio/laravel-storage-connect)
[![Quality Score](https://img.shields.io/scrutinizer/g/stechstudio/laravel-storage-connect.svg?style=flat-square)](https://scrutinizer-ci.com/g/stechstudio/laravel-storage-connect)
[![Total Downloads](https://img.shields.io/packagist/dt/stechstudio/laravel-storage-connect.svg?style=flat-square)](https://packagist.org/packages/stechstudio/laravel-storage-connect)

This package is designed to drastically simplify the process of authenticating to a user's cloud storage account uploading files.
 
 - Handles OAuth flow to authorize a cloud storage account
 - Serializes and handles storing/loading of connection details
 - Queues upload tasks
 - Retries upload failures with increasing backoff
 - Supports uploading from local filesystem, S3 hosted files (registers a stream wrapper), or any URL
 - Automatically disables a connection if storage quota is full, and then re-enables if space is freed up
 - Fires events for all activity, and can optionally log activity for you

Currently Dropbox and Google Drive are the supported cloud storage backends.

➔ [View full documentation](https://stechstudio.github.io/laravel-storage-connect/)