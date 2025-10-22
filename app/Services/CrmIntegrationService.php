<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrmIntegrationService
{
    /**
     * Send lead to HubSpot CRM
     */
    public static function sendToHubSpot(Lead $lead): bool
    {
        try {
            $apiKey = config('services.hubspot.api_key');
            if (!$apiKey) {
                Log::warning('HubSpot API key not configured');
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://api.hubapi.com/crm/v3/objects/contacts', [
                'properties' => [
                    'firstname' => explode(' ', $lead->full_name)[0] ?? '',
                    'lastname' => explode(' ', $lead->full_name)[1] ?? '',
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'company' => $lead->company_name,
                    'industry' => $lead->industry,
                    'lifecyclestage' => $lead->status,
                    'notes' => $lead->message,
                ],
            ]);

            if ($response->successful()) {
                Log::info('Lead sent to HubSpot', [
                    'lead_id' => $lead->id,
                    'hubspot_id' => $response->json('id'),
                ]);
                return true;
            }

            Log::error('HubSpot API error', [
                'lead_id' => $lead->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('HubSpot integration error', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send lead to Salesforce CRM
     */
    public static function sendToSalesforce(Lead $lead): bool
    {
        try {
            $clientId = config('services.salesforce.client_id');
            $clientSecret = config('services.salesforce.client_secret');
            $username = config('services.salesforce.username');
            $password = config('services.salesforce.password');
            $instanceUrl = config('services.salesforce.instance_url');

            if (!$clientId || !$clientSecret) {
                Log::warning('Salesforce credentials not configured');
                return false;
            }

            // Get access token
            $tokenResponse = Http::asForm()->post("$instanceUrl/services/oauth2/token", [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'username' => $username,
                'password' => $password,
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('Salesforce token error', [
                    'status' => $tokenResponse->status(),
                ]);
                return false;
            }

            $accessToken = $tokenResponse->json('access_token');

            // Create lead in Salesforce
            $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'Content-Type' => 'application/json',
            ])->post("$instanceUrl/services/data/v57.0/sobjects/Lead", [
                'FirstName' => explode(' ', $lead->full_name)[0] ?? '',
                'LastName' => explode(' ', $lead->full_name)[1] ?? '',
                'Email' => $lead->email,
                'Phone' => $lead->phone,
                'Company' => $lead->company_name,
                'Industry' => $lead->industry,
                'Description' => $lead->message,
            ]);

            if ($response->successful()) {
                Log::info('Lead sent to Salesforce', [
                    'lead_id' => $lead->id,
                    'salesforce_id' => $response->json('id'),
                ]);
                return true;
            }

            Log::error('Salesforce API error', [
                'lead_id' => $lead->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Salesforce integration error', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send lead to Pipedrive CRM
     */
    public static function sendToPipedrive(Lead $lead): bool
    {
        try {
            $apiToken = config('services.pipedrive.api_token');
            if (!$apiToken) {
                Log::warning('Pipedrive API token not configured');
                return false;
            }

            $response = Http::post('https://api.pipedrive.com/v1/persons', [
                'name' => $lead->full_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'org_id' => $lead->company_name,
                'api_token' => $apiToken,
            ]);

            if ($response->successful()) {
                Log::info('Lead sent to Pipedrive', [
                    'lead_id' => $lead->id,
                    'pipedrive_id' => $response->json('data.id'),
                ]);
                return true;
            }

            Log::error('Pipedrive API error', [
                'lead_id' => $lead->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Pipedrive integration error', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send lead to Zapier webhook
     */
    public static function sendToZapier(Lead $lead): bool
    {
        try {
            $webhookUrl = config('services.zapier.webhook_url');
            if (!$webhookUrl) {
                Log::warning('Zapier webhook URL not configured');
                return false;
            }

            $response = Http::post($webhookUrl, [
                'full_name' => $lead->full_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company_name' => $lead->company_name,
                'industry' => $lead->industry,
                'message' => $lead->message,
                'status' => $lead->status,
                'utm_source' => $lead->utm_source,
                'utm_medium' => $lead->utm_medium,
                'utm_campaign' => $lead->utm_campaign,
                'created_at' => $lead->created_at->toIso8601String(),
            ]);

            if ($response->successful()) {
                Log::info('Lead sent to Zapier', [
                    'lead_id' => $lead->id,
                ]);
                return true;
            }

            Log::error('Zapier webhook error', [
                'lead_id' => $lead->id,
                'status' => $response->status(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Zapier integration error', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send lead to all configured CRM systems
     */
    public static function sendToAllCrms(Lead $lead): array
    {
        $results = [];

        if (config('services.hubspot.enabled')) {
            $results['hubspot'] = self::sendToHubSpot($lead);
        }

        if (config('services.salesforce.enabled')) {
            $results['salesforce'] = self::sendToSalesforce($lead);
        }

        if (config('services.pipedrive.enabled')) {
            $results['pipedrive'] = self::sendToPipedrive($lead);
        }

        if (config('services.zapier.enabled')) {
            $results['zapier'] = self::sendToZapier($lead);
        }

        return $results;
    }
}
