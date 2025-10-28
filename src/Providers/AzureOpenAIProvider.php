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

        // Debug: Log the request payload
        Log::info('Azure OpenAI Request Payload', [
            'provider' => 'azure',
            'url' => $url,
            'model' => $this->model,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'system_message' => $this->systemMessage,
            'functions_count' => count($this->functions),
            'messages_count' => count($payload['messages']),
            'has_functions' => !empty($this->functions),
        ]);

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $message = $data['choices'][0]['message'];
                
                // Debug: Log the complete Azure response
                Log::info('Azure OpenAI Response', [
                    'provider' => 'azure',
                    'model' => $this->model,
                    'message' => $message,
                    'has_function_call' => isset($message['function_call']),
                    'usage' => $data['usage'] ?? null,
                ]);
                
                // Check if Azure wants to call a function
                if (isset($message['function_call'])) {
                    $functionName = $message['function_call']['name'];
                    $functionArgs = json_decode($message['function_call']['arguments'], true);
                    
                    // Debug: Log function call details
                    Log::info('Azure Function Call Detected', [
                        'provider' => 'azure',
                        'function_name' => $functionName,
                        'function_arguments' => $functionArgs,
                        'raw_arguments' => $message['function_call']['arguments'],
                    ]);
                    
                    // Execute the LaravelGPT function
                    $functionResult = $this->executeFunction($functionName, $functionArgs);
                    
                    // Debug: Log function execution result
                    Log::info('Function Execution Result', [
                        'provider' => 'azure',
                        'function_name' => $functionName,
                        'result' => $functionResult,
                        'result_type' => gettype($functionResult),
                    ]);
                    
                    // Send function result back to Azure
                    return $this->sendFunctionResult($functionName, $functionResult, $messages);
                }
                
                // Debug: Log normal response
                Log::info('Azure Normal Response', [
                    'provider' => 'azure',
                    'content' => $message['content'] ?? '',
                    'content_length' => strlen($message['content'] ?? ''),
                ]);
                
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
        // Debug: Log function execution start
        Log::info('Function Execution Started', [
            'provider' => 'azure',
            'function_name' => $functionName,
            'arguments' => $arguments,
            'arguments_count' => count($arguments),
        ]);
        
        try {
            // Create a function instance based on the function name
            $function = $this->createFunctionInstance($functionName);
            
            if (!$function) {
                Log::error('Function Instance Creation Failed', [
                    'provider' => 'azure',
                    'function_name' => $functionName,
                    'reason' => 'Function not found in function map',
                ]);
                return ['error' => 'Function not found: ' . $functionName];
            }
            
            // Debug: Log successful function instance creation
            Log::info('Function Instance Created', [
                'provider' => 'azure',
                'function_name' => $functionName,
                'function_class' => get_class($function),
            ]);
            
            // Use LaravelGPT FunctionManager to execute the function
            $functionManager = FunctionManager::make($function);
            
            // Debug: Log FunctionManager creation
            Log::info('FunctionManager Created', [
                'provider' => 'azure',
                'function_name' => $functionName,
                'function_manager_class' => get_class($functionManager),
            ]);
            
            $result = $functionManager->call($arguments);
            
            // Debug: Log function call result
            Log::info('Function Call Completed', [
                'provider' => 'azure',
                'function_name' => $functionName,
                'result_class' => get_class($result),
                'result_content' => $result->content,
                'result_type' => gettype($result->content),
            ]);
            
            return $result->content;
        } catch (\Exception $e) {
            Log::error('Function execution error', [
                'provider' => 'azure',
                'function' => $functionName,
                'arguments' => $arguments,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
        // Debug: Log function instance creation attempt
        Log::info('Creating Function Instance', [
            'provider' => 'azure',
            'function_name' => $functionName,
        ]);
        
        // Map function names to their corresponding LaravelGPT function classes
        $functionMap = [
            'application_case_query' => \MalteKuhr\LaravelGPT\Functions\ApplicationCaseQueryFunction::class,
            'appointment_stats' => \MalteKuhr\LaravelGPT\Functions\AppointmentStatsFunction::class,
            'visa_type_info' => \MalteKuhr\LaravelGPT\Functions\VisaTypeInfoFunction::class,
            'system_info' => \MalteKuhr\LaravelGPT\Functions\SystemInfoFunction::class,
        ];
        
        // Debug: Log available functions
        Log::info('Available Functions', [
            'provider' => 'azure',
            'function_map' => array_keys($functionMap),
            'requested_function' => $functionName,
            'function_exists' => isset($functionMap[$functionName]),
        ]);
        
        if (!isset($functionMap[$functionName])) {
            Log::warning('Function Not Found in Map', [
                'provider' => 'azure',
                'function_name' => $functionName,
                'available_functions' => array_keys($functionMap),
            ]);
            return null;
        }
        
        $functionClass = $functionMap[$functionName];
        
        // Debug: Log function class resolution
        Log::info('Function Class Resolved', [
            'provider' => 'azure',
            'function_name' => $functionName,
            'function_class' => $functionClass,
        ]);
        
        // Check if the function class exists
        if (!class_exists($functionClass)) {
            Log::error('Function Class Not Found', [
                'provider' => 'azure',
                'function_name' => $functionName,
                'function_class' => $functionClass,
                'class_exists' => false,
            ]);
            return null;
        }
        
        // Debug: Log successful class existence check
        Log::info('Function Class Exists', [
            'provider' => 'azure',
            'function_name' => $functionName,
            'function_class' => $functionClass,
            'class_exists' => true,
        ]);
        
        try {
            $instance = new $functionClass();
            
            // Debug: Log successful instance creation
            Log::info('Function Instance Created Successfully', [
                'provider' => 'azure',
                'function_name' => $functionName,
                'function_class' => $functionClass,
                'instance_class' => get_class($instance),
            ]);
            
            return $instance;
        } catch (\Exception $e) {
            Log::error('Function Instance Creation Failed', [
                'provider' => 'azure',
                'function_name' => $functionName,
                'function_class' => $functionClass,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
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
        // Debug: Log function result sending
        Log::info('Sending Function Result Back to Azure', [
            'provider' => 'azure',
            'function_name' => $functionName,
            'function_result' => $functionResult,
            'original_messages_count' => count($messages),
        ]);
        
        // Add the function call and result to the message history
        $functionCallMessage = [
            'role' => 'assistant',
            'function_call' => [
                'name' => $functionName,
                'arguments' => json_encode($functionResult),
            ],
        ];
        
        $functionResultMessage = [
            'role' => 'function',
            'name' => $functionName,
            'content' => json_encode($functionResult),
        ];
        
        $messages[] = $functionCallMessage;
        $messages[] = $functionResultMessage;
        
        // Debug: Log updated message history
        Log::info('Updated Message History', [
            'provider' => 'azure',
            'function_name' => $functionName,
            'new_messages_count' => count($messages),
            'added_function_call' => $functionCallMessage,
            'added_function_result' => $functionResultMessage,
        ]);
        
        // Make another API call with the function result
        Log::info('Making Follow-up API Call', [
            'provider' => 'azure',
            'function_name' => $functionName,
            'total_messages' => count($messages),
        ]);
        
        return $this->sendMessage($messages);
    }
}
