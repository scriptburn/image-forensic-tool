<?php
namespace ScriptBurn\Api\GoogleVison;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use GuzzleHttp\Exception\RequestException;

/**
 * Class Auth
 * @package App\Controller\Api
 */
class VisionApi
{
	private $db, $logger, $imageAnnotator, $visionService, $storageService, $client;
	// Inject Container in controller (which is bad, actually)
	public function __construct($appCredPath)
	{
		try
		{
			putenv('GOOGLE_APPLICATION_CREDENTIALS='.$appCredPath);
			$this->client = new \Google_Client();
			$this->client->useApplicationDefaultCredentials();
		}
		catch (\Exception $e)
		{
			return [0, "", $this->decodeResponseError($e)];
		}
	}

	function log($msg, $a = "", $b = "", $c = "", $e = "")
	{
		return;
		$this->logger->debug($msg);

		return $msg;
	}
	public function getClientInstance()
	{
		if (!$this->imageAnnotator)
		{
			//$this->imageAnnotator = new ImageAnnotatorClient(['restClientConfigPath' => __DIR__."/image_annotator_rest_client_config.php"]);
			$this->imageAnnotator = new ImageAnnotatorClient();
		}

		return $this->imageAnnotator;
	}
	function processImage($path)
	{
		try
		{
			$data = [

			];
			# annotate the image
			$response = $this->getClientInstance()->webDetection(file_get_contents($path));

			$web = $response->getWebDetection();
			if ($web)
			{
				$data = [
					'labels' => [],
					'pages_matching_images' => [],
					'full_matching_images' => [],
					'partial_matching_images' => [],
					'similar_matching_images' => [],
					'web_entities' => [],
				];

				//var_dump($web);
				//exit();
				// Print best guess labels

				foreach ($web->getBestGuessLabels() as $label)
				{
					$data['labels'][] = $label->getLabel();
				}

				// Print pages with matching images

				foreach ($web->getPagesWithMatchingImages() as $page)
				{
					$data['pages_matching_images'][] = $page->getUrl();
				}

				// Print full matching images

				foreach ($web->getFullMatchingImages() as $fullMatchingImage)
				{
					$data['full_matching_images'][] = $fullMatchingImage->getUrl();
				}

				// Print partial matching images

				foreach ($web->getPartialMatchingImages() as $partialMatchingImage)
				{
					$data['partial_matching_images'][] = $partialMatchingImage->getUrl();
				}

				// Print visually similar images

				foreach ($web->getVisuallySimilarImages() as $visuallySimilarImage)
				{
					$data['similar_matching_images'][] = $visuallySimilarImage->getUrl();
				}

				// Print web entities

				foreach ($web->getWebEntities() as $entity)
				{
					$data['web_entities'][] = [$entity->getDescription(), $entity->getScore()];
				}

				ob_start();
				// Print best guess labels
				printf('%d best guess labels found'.PHP_EOL,
					count($web->getBestGuessLabels()));
				foreach ($web->getBestGuessLabels() as $label)
				{
					printf('Best guess label: %s'.PHP_EOL, $label->getLabel());
				}
				print(PHP_EOL);

				// Print pages with matching images
				printf('%d pages with matching images found'.PHP_EOL,
					count($web->getPagesWithMatchingImages()));
				foreach ($web->getPagesWithMatchingImages() as $page)
				{
					printf('URL: %s'.PHP_EOL, $page->getUrl());
				}
				print(PHP_EOL);

				// Print full matching images
				printf('%d full matching images found'.PHP_EOL,
					count($web->getFullMatchingImages()));
				foreach ($web->getFullMatchingImages() as $fullMatchingImage)
				{
					printf('URL: %s'.PHP_EOL, $fullMatchingImage->getUrl());
				}
				print(PHP_EOL);

				// Print partial matching images
				printf('%d partial matching images found'.PHP_EOL,
					count($web->getPartialMatchingImages()));
				foreach ($web->getPartialMatchingImages() as $partialMatchingImage)
				{
					printf('URL: %s'.PHP_EOL, $partialMatchingImage->getUrl());
				}
				print(PHP_EOL);

				// Print visually similar images
				printf('%d visually similar images found'.PHP_EOL,
					count($web->getVisuallySimilarImages()));
				foreach ($web->getVisuallySimilarImages() as $visuallySimilarImage)
				{
					printf('URL: %s'.PHP_EOL, $visuallySimilarImage->getUrl());
				}
				print(PHP_EOL);

				// Print web entities
				printf('%d web entities found'.PHP_EOL,
					count($web->getWebEntities()));
				foreach ($web->getWebEntities() as $entity)
				{
					printf('Description: %s, Score %s'.PHP_EOL,
						$entity->getDescription(),
						$entity->getScore());
				}
				print(PHP_EOL);

				$data['text']=ob_get_clean();


			}

			return [1, $data, ""];
		}
		catch (\Exception $e)
		{
			return [0, "", $this->decodeResponseError($e)];
		}
		catch (\DomainException $e)
		{
			return [0, "", $this->decodeResponseError($e)];
		}
	}
	function getVisionService()
	{
		if (!$this->visionService)
		{
			$this->client->addScope(\Google_Service_Vision::CLOUD_PLATFORM);
			$this->client->addScope(\Google_Service_Vision::CLOUD_VISION);
			$this->visionService = new \Google_Service_Vision($this->client);
		}

		return $this->visionService;
	}
	function getStorageService()
	{
		if (!$this->storageService)
		{
			$this->client->addScope(\Google_Service_Storage::DEVSTORAGE_READ_WRITE);
			$this->storageService = new \Google_Service_Storage($this->client);
		}

		return $this->storageService;
	}

	public function deleteFileFromBucket($bucket, $filePath)
	{
		try {
			$result = $this->getStorageService()->objects->delete($bucket, $filePath);

			return [1, get_object_vars($result)];
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error')];
		}
	}
	public function getFileFormBucket($bucket, $name, $options = [])
	{
		try
		{
			$result = $this->getStorageService()->objects->get($bucket, $name, $options);

			return [1, get_class($result) == 'GuzzleHttp\Psr7\Response' ? (string) $result->getBody() : get_object_vars($result)];
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error')];
		}
	}
	public function uploadFileToBucket($bucket, $file)
	{
		try
		{
			if (!is_array($file) ||
				empty($file['type']) ||
				empty($file['file']) ||
				($file['type'] != 'file' && (empty($file['name']) || empty($file['file'])))
			)
			{
				throw new \Exception("Invalid arguments passed");
			}

			if ($file['type'] == 'file')
			{
				if (!@filesize($file['file']))
				{
					throw new \Exception("File to upload does not exists:".$file['file']);
				}
				else
				{
					$postbody['name'] = empty($file['name']) ? basename($file['file']) : ($file['name']);
					$postbody['data'] = file_get_contents($file['file']);
				}
			}
			else
			{
				$postbody['name'] = $file['name'];
				$postbody['data'] = $file['file'];
			}
			$postbody['uploadType'] = 'media';

			$gsso = new \Google_Service_Storage_StorageObject();
			$gsso->setName($postbody['name']);

			$result = $this->getStorageService()->objects->insert($bucket, $gsso, $postbody);
			$this->log("Uploaded", "", __FUNCTION__, __LINE__, 'info');

			return [1, get_object_vars($result)];
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error')];
		}
	}

	function parseVisonAPiJsonResponse($bucketName, $name)
	{
		try
		{
			$resp = $this->getFileFormBucket($bucketName, $name, ['alt' => 'media']);
			if (!$resp[0])
			{
				throw new \Exception($resp[1]);
			}
			/*
				$file = explode("/", $name);
				$fileName = $file[count($file) - 1];
				unset($file[count($file) - 1]);
				$file = implode("/", $file);
				$fol = storage_path('framework/cache/'.$this->createSlug($file));
				if (!file_exists($fol))
				{
					mkdir($fol);
				}
				file_put_contents(rtrim($fol)."/".$fileName, $resp[1]);
			*/
			$object = json_decode($resp[1], 1);

			$pages = [];

			if (!empty($object['responses']))
			{
				foreach ($object['responses'] as $item)
				{
					$pages[] = [$item['fullTextAnnotation']['text'], $item['context']['pageNumber']];
				}
			}

			return [1, $pages];
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error')];
		}
	}

	function processPDFCached($filePath, $bucketName, $tmpPath = "")
	{
		try
		{
			$from_cache = 0;
			$hash = $this->generateInputPdfHash($filePath);
			if (!$hash[0])
			{
				throw new \Exception("Invalid input file: ".$filePath);
			}
			$json = Json::whereUrl($hash[1])->get();

			if (!$json || !count($json))
			{
				$ret = $this->processPDF($filePath, $bucketName, $tmpPath, $hash[0]);

				if (!$ret[0])
				{
					return $ret;
				}
				$jsonData = $ret[1];

				$ret = array_chunk($jsonData, 10);
				foreach ($ret as $item)
				{
					Json::insert($item);
				}
			}
			else
			{
				$this->log("Found in cache: $filePath");
				$from_cache = 1;
			}
			$json = Json::select(['page', 'text', 'url'])->whereUrl($hash[1])->get();
			$jsonData = $json->toArray();

			return [1, $jsonData, $from_cache];
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error'), $from_cache];
		}
	}

	function generateInputPdfHash($filePath)
	{
		if (strtolower(substr($filePath, 0, 7)) == 'http://' || strtolower(substr($filePath, 0, 8)) == 'https://')
		{
			return [md5($filePath), $filePath];
		}
		elseif (strtolower(substr($filePath, 0, 5)) != 'gs://')
		{
			if (!file_exists($filePath))
			{
				throw new \Exception("Unable to find input file: ".$filePath);
			}
			else
			{
				return [md5_file($filePath), $filePath];
			}
		}
		elseif (strtolower(substr($filePath, 0, 5)) == 'gs://')
		{
			return [md5($filePath), $filePath];
		}
	}

	function processPDF($filePath, $bucketName, $tmpPath = "", $hash = "")
	{
		$input = $filePath;
		$cancelLast = true;
		if (!$tmpPath)
		{
			$tmpPath = sys_get_temp_dir();
		}

		try
		{
			if (strtolower(substr($filePath, 0, 7)) == 'http://' || strtolower(substr($filePath, 0, 8)) == 'https://')
			{
				$this->log("Downloading remote file: ".$filePath);
				$response = $this->downloadFile($filePath, "", $tmpPath);
				if (!$response[0])
				{
					throw new \Exception("Unable to download remote file: ".$response[1]);
				}
				$filePath = $response[1]."/".$response[2];
			}

			if (strtolower(substr($filePath, 0, 5)) != 'gs://')
			{
				if (file_exists($filePath))
				{
					$pathinfo = pathinfo($filePath);
					$hash = md5_file($filePath);
					$slugBaseName = substr($this->createSlug($pathinfo['basename']), 0, 1024);
					$slugName = substr($this->createSlug($pathinfo['filename']), 0, 1024);
					$folder = $hash.substr($slugBaseName, 0, 1024 - (strlen($hash)));

					$bucketFolder = "visionapi/".$folder."/input/".$slugName.($pathinfo['extension'] ? ".".$pathinfo['extension'] : "");

					$doesExists = $this->getFileFormBucket($bucketName, $bucketFolder);

					if (!$doesExists[0])
					{
						$this->log("Uploading file to bucket: ".$filePath);
						$ret = $this->uploadFileToBucket($bucketName, ['type' => 'file', 'file' => $filePath, 'name' => $bucketFolder]);
						if (!$ret[0])
						{
							throw new \Exception($ret[1]);
						}
					}
					$inputGsFile = "gs://".$bucketName."/".$bucketFolder;
					$outputGsFolder = "gs://".$bucketName."/visionapi/".$folder."/output/";
				}
				else
				{
					throw new \Exception("Unable to find input file: ".$filePath);
				}
			}
			else
			{
				$inputGsFile = $filePath;
				$path = explode("/", $inputGsFile);
				unset($path[0]);
				unset($path[1]);
				unset($path[2]);
				$path = implode("/", $path);

				$pathinfo = pathinfo($path);
				$hash = md5(basename($path));

				$slugBaseName = substr($this->createSlug($pathinfo['basename']), 0, 1024);
				$slugName = substr($this->createSlug($pathinfo['filename']), 0, 1024);
				$folder = $hash.substr($slugBaseName, 0, 1024 - (strlen($hash)));
				$outputGsFolder = "gs://".$bucketName."/visionapi/".$folder."/output/";
			}
			// /p_d($inputGsFile."-".$outputGsFolder);
			$colec = new \Google_Service_Vision_AsyncBatchAnnotateFilesRequest();
			$imgContext = new \Google_Service_Vision_ImageContext();

			$gcsSource = new \Google_Service_Vision_GcsSource();
			$gcsSource->setUri($inputGsFile);
			$inputConfig = new \Google_Service_Vision_InputConfig();
			$inputConfig->setGcsSource($gcsSource);
			$inputConfig->setMimeType("application/pdf");

			$gcsDest = new \Google_Service_Vision_GcsDestination();
			$gcsDest->setUri($outputGsFolder);
			$outputConfig = new \Google_Service_Vision_OutputConfig();
			$outputConfig->setGcsDestination($gcsDest);
			$outputConfig->setBatchSize(1);

			$inputFeature = new \Google_Service_Vision_Feature();
			$inputFeature->setType("DOCUMENT_TEXT_DETECTION");

			$fileReq = new \Google_Service_Vision_AsyncAnnotateFileRequest();

			$fileReq->setInputConfig($inputConfig);
			$fileReq->setOutputConfig($outputConfig);
			$fileReq->setFeatures($inputFeature);
			$fileReq->setImageContext($imgContext);
			$colec->setRequests($fileReq);
			$result = $this->getVisionService()->files->asyncBatchAnnotate($colec);
			$_SESSION['lastvision_op'] = $result->getName();
			$opreation = $this->getOpreationResult($_SESSION['lastvision_op'], 1);
			if (!$opreation[0])
			{
				throw new \Exception($opreation[1]);
			}
			unset($opreation[0]);
			$fol = rtrim(substr($opreation[3], strlen("gs://".$bucketName."/")), "/");
			$files = $this->getFilesInBucketFolder($bucketName, $fol);
			if (!$files[0])
			{
				throw new \Exception($files[1]);
			}

			$json = [];
			$date = date('Y-m-d H:i:s', time());
			foreach ($files[1] as $file)
			{
				$this->log("Reading file: ".$file['name']);
				$file = $this->parseVisonAPiJsonResponse($bucketName, $file['name']);
				if (!$file[0])
				{
					throw new \Exception($file[1]);
				}
				foreach ($file[1] as $item)
				{
					$json[] = ["text" => $item[0], 'page' => $item[1], 'url' => $input, 'hash' => $hash, 'created_at' => $date, 'updated_at' => $date];
				}
			}

			return [1, $json];
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error')];
		}
	}
	function getFilesInBucketFolder($bucket, $folder)
	{
		try
		{
			$result = $this->getStorageService()->objects->listObjects($bucket, ['prefix' => $folder]);
			$result = $this->getApiResponseItems($result, 'Google_Service_Storage_Objects');
			if (!$result[0])
			{
				throw new \Exception($result[1]);
			}

			return [1, ($result[1])];
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error')];
		}
	}
	function getApiResponseItems($item, $objectType, $index = "name")
	{
		try
		{
			if (is_array($item))
			{
				return $item;
			}

			if (get_class($item) != $objectType)
			{
				throw new Exception("Unknown response object");
			}

			$items = [];
			foreach ($item->getItems() as $item)
			{
				$vl = get_object_vars($item);
				if (isset($vl[$index]))
				{
					$items[$vl[$index]] = $vl;
				}
				else
				{
					$items[] = $vl;
				}
			}

			return [1, $items];
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error')];
		}
	}
	function cancelVisionOpreation($name)
	{
		try
		{
			$this->log("Caceling opreation: ".$name);
			$cancelOpreation = new \Google_Service_Vision_CancelOperationRequest();
			$response = $this->getVisionService()->operations->cancel($name, $cancelOpreation);

			return [1, $response];
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error')];
		}
	}
	function getOpreationResult($name, $sleep = 0)
	{
		$loop = 0;
		try
		{
			$this->log("Opreation name: ".$name);
			do
			{
				$loop++;
				$this->log($loop.". Checking status");
				$opreationResult = $this->getVisionService()->operations->get($name);
				$meta = $opreationResult->getMetaData();

				if ($opreationResult->getDone())
				{
					$error = $opreationResult->getError();
					if ($error)
					{
						$this->log("Opreation error: ".$response->getMessage());

						return [0, $opreationResult, $meta['state'], [$response->getMessage(), $response->getDetails()]];
					}
					else
					{
						$response = $opreationResult->getResponse();
						$this->log("Opreation result: ".$response['responses'][0]['outputConfig']['gcsDestination']['uri']);

						return [1, $opreationResult, $meta['state'], $response['responses'][0]['outputConfig']['gcsDestination']['uri']];
					}
				}
				else
				{
					$this->log("Opreation status: ".$meta['state']);
					if ($sleep)
					{
						$this->log("Sleeping for: ".$sleep);
						sleep($sleep);
					}
					else
					{
						return [2, $opreationResult, $meta['state'], ""];
					}
				}
			} while (true);
		}
		catch (\Exception $e)
		{
			return [0, $this->log($this->decodeResponseError($e), "", __FUNCTION__, __LINE__, 'error')];
		}
	}

	function downloadFile($url, $name = "", $outPutFolder = "")
	{
		if (!$name)
		{
			$name = basename($url);
		}
		if (!$outPutFolder)
		{
			$outPutFolder = sys_get_temp_dir();
		}
		$path = rtrim($outPutFolder, "/").'/'.$name;
		$file_path = fopen($path, 'w');
		try
		{
			$client = new \GuzzleHttp\Client();
			$response = $client->get($url, ['save_to' => $file_path]);
			if (($status = $response->getStatusCode()) !== 200)
			{
				throw new \Exception("Unable to download the file:".$status);
			}

			return [1, $outPutFolder, $name];
		}
		catch (\Exception $e)
		{
			return [0, $e->getMessage()];
		}
		catch (RequestException $e)
		{
			if ($e->hasResponse())
			{
				return [0, (string) $e->getResponse()->getBody(), $e->getCode()];
			}
			else
			{
				return [0, $e->getMessage(), 503];
			}
		}
	}

	function detect_document_text($path)
	{
		$imageAnnotator = new ImageAnnotatorClient(['restClientConfigPath' => __DIR__."/image_annotator_rest_client_config.php"]);

		# annotate the image
		$image = file_get_contents($path);
		$response = $imageAnnotator->documentTextDetection($image);
		$annotation = $response->getFullTextAnnotation();
		p_d($annotation, 1);
		# print out detailed and structured information about document text
		if ($annotation)
		{
			foreach ($annotation->getPages() as $page)
			{
				foreach ($page->getBlocks() as $block)
				{
					$block_text = '';
					foreach ($block->getParagraphs() as $paragraph)
					{
						foreach ($paragraph->getWords() as $word)
						{
							foreach ($word->getSymbols() as $symbol)
							{
								$block_text .= $symbol->getText();
							}
							$block_text .= ' ';
						}
						$block_text .= "\n";
					}
					printf('Block content: %s', $block_text);
					printf('Block confidence: %f'.PHP_EOL,
						$block->getConfidence());

					# get bounds
					$vertices = $block->getBoundingBox()->getVertices();
					$bounds = [];
					foreach ($vertices as $vertex)
					{
						$bounds[] = sprintf('(%d,%d)', $vertex->getX(),
							$vertex->getY());
					}
					print('Bounds: '.join(', ', $bounds).PHP_EOL);
					print(PHP_EOL);
				}
			}
		}
		else
		{
			print('No text found'.PHP_EOL);
		}
	}
	function recordImagelabels($imagPath)
	{
		$labels = $this->processImage($imagPath);

		if (!$labels[0])
		{
			$this->setImageData($imagPath, $labels[1], $labels[2]);

			return $labels;
		}
		else
		{
			return $this->setImageData($imagPath, $labels[1], $labels[2]);
		}
	}

	function searcKeyword($keyword, $split = true)
	{
		try
		{
			$selectStatement = $this->db->select()
				->from('files');
			if ($keyword)
			{
				$keywords = is_array($keyword) ? $keyword : ($split ? explode(",", $keyword) : [$keyword]);
				foreach ($keywords as $index => $keyword)
				{
					if (!$index)
					{
						$selectStatement->where('keywords', 'like', "%,$keyword,%");
					}
					else
					{
						$selectStatement->orWhere('keywords', 'like', "%,$keyword,%");
					}
				}
			}

			$stmt = $selectStatement->execute();
			$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$bucket = getenv('GOOGLE_CLOUD_IMAGE_STORE_BUCKET');
			foreach ($rows as $index => $row)
			{
				$rows[$index]['title'] = basename($row['name']);
				$row['keywords'] = array_filter(explode(",", trim($row['keywords'])));

				foreach ($row['keywords'] as $keyIndex => $key)
				{
					$found = in_array($key, $keywords);
					$row['keywords'][$keyIndex] = "<div ".(!$found ? "title='search images with keyword: $key '" : '')." ".(!$found ? 'style="cursor: pointer;"' : '')."  class='chip  ".($found ? 'blue-text' : ' keyword-chip')."' data-keyword='$key'>".$key."</div>";
				}
				$rows[$index]['keywords'] = implode(" ", $row['keywords']);

				//	$rows[$index]['keywords'] = implode(",", array_filter(explode(",", trim($row['keywords']))));
				$rows[$index]['image'] = "https://storage.googleapis.com/".substr($row['name'], 5);
			}

			return $rows;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}
	function deleteImageData($id)
	{
		try
		{
			$deleteStatement = $this->db->delete()
				->from('files')
				->where('id', '=', $id);

			return $deleteStatement->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}
	}
	function getImageData($name)
	{
		try
		{
			$selectStatement = $this->db->select()
				->from('files')
				->where('name', '=', $name);

			$stmt = $selectStatement->execute();
			$data = $stmt->fetch();

			return $data;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	function setImageData($name, $keywords, $error = "")
	{
		try
		{
			$keywords = is_array($keywords) ? ",".implode(",", $keywords)."," : $keywords;
			$rowData = $this->getImageData($name);

			if ($rowData)
			{
				$fieldData = [
					'keywords' => $keywords,
					'error' => $error,
					'time_updated' => time()];
				if ($error)
				{
					unset($fieldData['keywords']);
				}

				$updateStatement = $this->db->update($fieldData)
					->table('files')
					->where('name', '=', $name);
				$affectedRows = $updateStatement->execute();
				$fieldData = array_merge($rowData, $fieldData);
			}
			else
			{
				$fieldData = array('name' => $name, 'keywords' => $keywords, 'time_created' => time(), 'time_updated' => time(), 'error' => $error);

				$insertStatement = $this->db->insert(array_keys($fieldData))
					->into('files')
					->values(array_values($fieldData));

				$insertStatement->execute(false);

				$fieldData['id'] = $this->db->lastInsertId();
			}

			return [1, $fieldData];
		}
		catch (\Exception $e)
		{
			return [0, $e->getMessage()];
		}
	}
	function decodeResponseError($e, $searchReplace = [])
	{
		$msg = $e->getMessage();
		$errObj = json_decode($e->getMessage(), JSON_OBJECT_AS_ARRAY);

		$fnMatchthis = function ($rule, $err, $searchReplace = [])
		{
			$matches = [];
			$match = preg_match_all($rule, $err, $matches, PREG_SET_ORDER, 0);
			//p_d($matches[0][1]);
			if (!empty($matches[0][1]))
			{
				$str = explode("/", $matches[0][1]);
				$err = str_replace($matches[0][1], $str[count($str) - 1], $err);
				foreach ($searchReplace as $key => $value)
				{
					$err = str_replace($key, $value, $err);
				}
			}

			return $err;
		};
		if (is_array($errObj))
		{
			$err = "";
			if (!empty($errObj['error']['errors']))
			{
				$err = $errObj['error']['errors'][0]['message'];
			}
			elseif (!empty($errObj['error_description']))
			{
				$err = $errObj['error_description'];
			}

			if ($err)
			{
				if (stripos($err, 'Invalid JWT Signature.') !== false)
				{
					$err = "You do not have permission on this project"; //"Your access to the project: '{$this->project}' seems to be revoked";
				}
				else
				{
					$err = $fnMatchthis("/The resource ['](.*)['] already exists/", $err, $searchReplace);

					$err = $fnMatchthis("/The resource ['](.*)['] was not found/", $err, $searchReplace);
				}

				return $err;
			}
		}

		return $msg;
	}
	function sync_bucket($uploaded_file)
	{
		if (!file_exists($uploaded_file))
		{
			$this->logger->info("Uploaded file '$uploaded_file' does not exists, exiting");

			return false;
		}
		elseif (!@is_array(getimagesize($uploaded_file)))
		{
			$this->logger->info("Uploaded file is not an image, exiting");

			return false;
		}
		$base_gs = "gs://".getenv('GOOGLE_CLOUD_IMAGE_STORE_BUCKET');
		$base_ftp_dir = '/home/FTP';
		$base_mount_dir = "/mnt/gcfuse";

		//$base_ftp_dir = "/var/www/clients/upwork/pending/Google_Cloud_Vision_API_01edc7aec7a8c00dff/log/test/home/FTP";
		//$base_mount_dir = "/var/www/clients/upwork/pending/Google_Cloud_Vision_API_01edc7aec7a8c00dff/log/test/mnt/gcfuse";

		$path_parts = pathinfo($uploaded_file);

		$file_relative_dir = array_values(array_filter(explode("/", substr($path_parts['dirname'], strlen($base_ftp_dir)))));
		unset($file_relative_dir[0]);
		$file_relative_dir = (count($file_relative_dir) ? "/".implode("/", $file_relative_dir)."/" : "/");
		$file_relative_path = $file_relative_dir.basename($uploaded_file);

		$file_relative_dir_encoded = explode("/", $file_relative_dir);
		foreach ($file_relative_dir_encoded as $key => $value)
		{
			$file_relative_dir_encoded[$key] = urlencode($value);
		}
		$file_relative_dir_encoded = implode("/", $file_relative_dir_encoded);
		$file_relative_path_encoded = $file_relative_dir_encoded.urlencode(basename($uploaded_file));

		$folder_path_in_mount = $base_mount_dir.$file_relative_dir;
		$file_path_in_mount = $base_mount_dir.$file_relative_path;

		$file_path_in_bucket = $base_gs.$file_relative_path_encoded;

		if (!is_dir($folder_path_in_mount))
		{
			mkdir($folder_path_in_mount);
		}
		if (file_exists($file_path_in_mount))
		{
			unlink($file_path_in_mount);
		}
		$this->logger->info("Uploading '$file_relative_path' to bucket '".getenv('GOOGLE_CLOUD_IMAGE_STORE_BUCKET')."' as '$file_relative_path_encoded'");
		if (copy($uploaded_file, $file_path_in_mount))
		{
			$this->logger->info("Calling Google vision API on uploaded image");
			$ret = ($this->recordImagelabels($file_path_in_bucket));
			if (!$ret[0])
			{
				$this->logger->info("Google vision API returned with error: ".$ret[1]);
			}
			else
			{
				$this->logger->info("Google vision API returned with image keywords: ".@$ret[1]['keywords']);
				$this->logger->info("Storing image keywords in database for future search");
			}
		}
		else
		{
			$this->logger->error("Copying Failed ");
		}
	}
	function createSlug($str, $delimiter = '-')
	{
		$slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));

		return $slug;
	}
}
