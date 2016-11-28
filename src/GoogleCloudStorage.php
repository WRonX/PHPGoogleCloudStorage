<?php
namespace WRonX\Utils\Google;

use Google_Client;
use Google_Service_Exception;
use Google_Service_Storage;
use Google_Service_Storage_StorageObject;
use GuzzleHttp\Message\MessageFactory;
use Symfony\Component\HttpFoundation\Response;

class GoogleCloudStorage
{
    /**
     * @var $bucketName string
     */
    private $bucketName;
    
    /**
     * @var $authFilePath string
     */
    private $authFilePath;
    
    /**
     * @var $lastFilePath string
     */
    private $lastFilePath;
    
    /**
     * @var $googleClient Google_Client
     */
    private $googleClient;
    
    /**
     * @var $googleStorageService Google_Service_Storage
     */
    private $googleStorageService;
    
    /**
     * @var $googleStorageObject Google_Service_Storage_StorageObject
     */
    private $googleStorageObject;
    
    /**
     * @var $cacheStorageObjects boolean
     */
    private $cacheStorageObjects;
    
    public function __construct($authFilePath = "", $bucketName = "", $cacheStorageObjects = true)
    {
        $this->setAuthFilePath($authFilePath);
        $this->setBucketName($bucketName);
        $this->setCacheStorageObjects($cacheStorageObjects);
        
        $this->initializeGoogleObjects();
    }
    
    private function initializeGoogleObjects()
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setAuthConfig($this->getAuthFilePath());
        $this->googleClient->setScopes(array(
                                           'https://www.googleapis.com/auth/devstorage.read_write',
                                       ));
        
        $this->googleStorageService = new Google_Service_Storage($this->googleClient);
    }
    
    /**
     * @return string
     */
    public function getAuthFilePath()
    {
        return $this->authFilePath;
    }
    
    /**
     * @param string $authFilePath
     * @return GoogleCloudStorage
     */
    public function setAuthFilePath($authFilePath)
    {
        $this->authFilePath = $authFilePath;
        
        $this->initializeGoogleObjects();
        
        return $this;
    }
    
    /**
     * @param $filePath string
     */
    public function deleteFile($filePath)
    {
        $this->googleStorageService->objects->delete(
            $this->getBucketName(),
            $filePath
        );
    }
    
    /**
     * @return string
     */
    public function getBucketName()
    {
        return $this->bucketName;
    }
    
    /**
     * @param string $bucketName
     * @return GoogleCloudStorage
     */
    public function setBucketName($bucketName)
    {
        $this->bucketName = $bucketName;
        
        return $this;
    }
    
    public function getFile($filePath, $contentOnly = true, $downloadFileName = "file")
    {
        if(!$this->fileExists($filePath))
            return null;
        
        $httpClient = $this->googleClient->authorize();
        $request = (new MessageFactory())->createRequest('GET', $this->googleStorageObject->getMediaLink());
        $response = $httpClient->send($request);
        
        if($contentOnly)
            return $response->getBody();
        
        return new Response(
            $response->getBody(),
            Response::HTTP_OK,
            array(
                'Content-Type' => $response->getHeaders()['Content-Type'],
                'Content-Transfer-Encoding' => 'Binary',
                'Content-disposition' => 'attachment; filename=' . preg_replace("/[^A-Za-z0-9]/", '_', $downloadFileName),
            )
        );
    }
    
    /**
     * @param $filePath string
     * @return bool
     */
    public function fileExists($filePath)
    {
        try
        {
            $this->getObject($filePath);
        }
        catch(Google_Service_Exception $googleServiceException)
        {
            if($googleServiceException->getCode() == 404)
                return false;
        }
        
        return true;
    }
    
    /**
     * Save last object, useful sometimes
     *
     * @param $filePath string
     * @throws Google_Service_Exception
     */
    private function getObject($filePath)
    {
        if($this->getCacheStorageObjects())
        {
            if($filePath == $this->lastFilePath)
                return;
        }
        
        $this->googleStorageObject = new Google_Service_Storage_StorageObject(); // property with empty object, just in case
        
        $this->lastFilePath = $filePath;
        try
        {
            $this->googleStorageObject = $this->googleStorageService->objects->get($this->getBucketName(), $filePath);
        }
        catch(Google_Service_Exception $googleServiceException)
        {
            $this->lastFilePath = null;
            throw $googleServiceException;
        }
    }
    
    /**
     * @return boolean
     */
    public function getCacheStorageObjects()
    {
        return $this->cacheStorageObjects;
    }
    
    /**
     * @param boolean $cacheStorageObjects
     * @return GoogleCloudStorage
     */
    public function setCacheStorageObjects($cacheStorageObjects)
    {
        $this->cacheStorageObjects = $cacheStorageObjects;
        
        return $this;
    }
    
    /**
     * @param $filePath string
     * @return integer
     */
    public function getFileSize($filePath)
    {
        if(!$this->fileExists($filePath))
            return null;
        
        return $this->googleStorageObject->size;
    }
    
    /**
     * @param $sourceFileName string
     * @param $targetFileName string
     */
    public function uploadFile($sourceFileName, $targetFileName)
    {
        $newGoogleStorageObject = new Google_Service_Storage_StorageObject();
        $newGoogleStorageObject->setName($targetFileName);
        
        $this->googleStorageService->objects->insert(
            $this->getBucketName(),
            $newGoogleStorageObject,
            array(
                'data' => file_get_contents($sourceFileName),
                'mimeType' => mime_content_type($sourceFileName),
                'uploadType' => 'multipart',
                'predefinedAcl' => 'authenticatedRead',
            )
        );
    }
}
