<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MalteKuhr\LaravelGPT\FunctionManager;

class AzureOpenAIProvider extends BaseAiProvider
{
    public function getId(): string
    {
        return 'azure';
    }

    public function getName(): string
    {
        return 'Azure OpenAI';
    }

    public function getDefaultModel(): string
    {
        return 'gpt-4o-mini';
    }

    public function getAvailableModels(): array
    {
        return [
            'gpt-4o' => 'GPT-4 Omni',
            'gpt-4o-mini' => 'GPT-4 Omni Mini',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-35-turbo' => 'GPT-3.5 Turbo',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['api_key']) && 
               !empty($this->config['endpoint']) && 
               !empty($this->config['deployment_name']);
    }

    public function getConfigurationFields(): array
    {
        return [
            'api_key' => [
                'type' => 'password',
                'label' => 'API Key',
                'required' => true,
                'env_key' => 'AZURE_OPENAI_API_KEY',
            ],
            'endpoint' => [
                'type' => 'url',
                'label' => 'Endpoint URL',
                'required' => true,
                'env_key' => 'AZURE_OPENAI_ENDPOINT',
                'placeholder' => 'https://your-resource.openai.azure.com/',
            ],
            'deployment_name' => [
                'type' => 'text',
                'label' => 'Deployment Name',
                'required' => true,
                'env_key' => 'AZURE_OPENAI_DEPLOYMENT_NAME',
            ],
            'api_version' => [
                'type' => 'text',
                'label' => 'API Version',
                'required' => false,
                'default' => '2024-02-15-preview',
                'env_key' => 'AZURE_OPENAI_API_VERSION',
            ],
        ];
    }

    public function validateConfiguration(array $config): bool
    {
        return !empty($config['api_key']) && 
               !empty($config['endpoint']) && 
               !empty($config['deployment_name']);
    }

    public function sendMessage(array $messages, array $config = []): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Azure OpenAI is not properly configured');
        }

        $apiKey = $this->config['api_key'];
        $endpoint = rtrim($this->config['endpoint'], '/');
        $deploymentName = $this->config['deployment_name'];
        $apiVersion = $this->config['api_version'] ?? '2024-02-15-preview';

        $url = "{$endpoint}/openai/deployments/{$deploymentName}/chat/completions?api-version={$apiVersion}";

        $payload = [
            'messages' => $messages,
            'model' => $this->model,
            'temperature' => $this->temperature,
        ];

        if ($this->maxTokens) {
            $payload['max_tokens'] = $this->maxTokens;
        }

        if ($this->systemMessage) {
            array_unshift($payload['messages'], [
                'role' => 'system',
                'content' => $this->systemMessage,
            ]);
        }

        if (!empty($this->functions)) {
            $payload['functions'] = $this->functions;
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $message = $data['choices'][0]['message'];
                
                // Check if Azure wants to call a function
                if (isset($message['function_call'])) {
                    $functionName = $message['function_call']['name'];
                    $functionArgs = json_decode($message['function_call']['arguments'], true);
                    
                    // Execute the LaravelGPT function
                    $functionResult = $this->executeFunction($functionName, $functionArgs);
                    
                    // Send function result back to Azure
                    return $this->sendFunctionResult($functionName, $functionResult, $messages);
                }
                
                // Normal response
                return [
                    'success' => true,
                    'content' => $message['content'] ?? '',
                    'usage' => $data['usage'] ?? null,
                ];
            } else {
                Log::error('Azure OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'API request failed: ' . $response->status(),
                    'content' => 'Sorry, I encountered an error. Please try again.',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Azure OpenAI Request Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => 'Sorry, I encountered an error. Please try again.',
            ];
        }
    }

    /**
     * Execute a function call using LaravelGPT FunctionManager.
     *
     * @param string $functionName
     * @param array $arguments
     * @return array
     */
    private function executeFunction(string $functionName, array $arguments): array
    {
        try {
            // Create a function instance based on the function name
            $function = $this->createFunctionInstance($functionName);
            
            if (!$function) {
                return ['error' => 'Function not found: ' . $functionName];
            }
            
            // Use LaravelGPT FunctionManager to execute the function
            $functionManager = FunctionManager::make($function);
            $result = $functionManager->call($arguments);
            
            return $result->content;
        } catch (\Exception $e) {
            Log::error('Function execution error', [
                'function' => $functionName,
                'arguments' => $arguments,
                'error' => $e->getMessage(),
            ]);
            
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a function instance based on the function name.
     *
     * @param string $functionName
     * @return mixed|null
     */
    private function createFunctionInstance(string $functionName)
    {
        // Map function names to their corresponding LaravelGPT function classes
        $functionMap = [
            'application_case_query' => \MalteKuhr\LaravelGPT\Functions\ApplicationCaseQueryFunction::class,
            'appointment_stats' => \MalteKuhr\LaravelGPT\Functions\AppointmentStatsFunction::class,
            'visa_type_info' => \MalteKuhr\LaravelGPT\Functions\VisaTypeInfoFunction::class,
            'system_info' => \MalteKuhr\LaravelGPT\Functions\SystemInfoFunction::class,
        ];
        
        if (!isset($functionMap[$functionName])) {
            return null;
        }
        
        $functionClass = $functionMap[$functionName];
        
        // Check if the function class exists
        if (!class_exists($functionClass)) {
            Log::warning('Function class not found', [
                'function' => $functionName,
                'class' => $functionClass,
            ]);
            return null;
        }
        
        return new $functionClass();
    }

    /**
     * Send function result back to Azure OpenAI.
     *
     * @param string $functionName
     * @param array $functionResult
     * @param array $messages
     * @return array
     */
    private function sendFunctionResult(string $functionName, array $functionResult, array $messages): array
    {
        // Add the function call and result to the message history
        $messages[] = [
            'role' => 'assistant',
            'function_call' => [
                'name' => $functionName,
                'arguments' => json_encode($functionResult),
            ],
        ];
        
        $messages[] = [
            'role' => 'function',
            'name' => $functionName,
            'content' => json_encode($functionResult),
        ];
        
        // Make another API call with the function result
        return $this->sendMessage($messages);
    }
}
