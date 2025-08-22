<?php
/*
* Utilization Factor(uf)
* Author: Pablo Morales Ferreira
* Company: Cinvestav-Tamaulipas
*/

define('NODES_REQUIRED_PUSH', 5);
define('NODES_REQUIRED_PULL', 3);

/**
 * [upload description]
 * @param  [type] $userId    [description]
 * @param  [type] $fileId    [description]
 * @param  [type] $chunkId   [description]
 * @param  [type] $chunkSize [description]
 * @return [type]            [description]
 */
function uploadChunksReliability($userId, $fileId, $fileName, $fileSize, $nodesRequired)
{
  //global $db;
  $db = new DbHandler();
  $chunkSize = intval(ceil($fileSize / $nodesRequired));
  //obtener nodos
  $nodes = $db->getNodesActive();
  $nodesTotal = count($nodes);
  $result = false;
  if ($nodesTotal >= NODES_REQUIRED_PUSH) {
    for ($i = 0; $i < $nodesTotal; $i++) {
      $nodes[$i]->setUf($chunkSize);
    }
    usort($nodes, array('Node', 'compare'));
    for ($i = 1; $i <= NODES_REQUIRED_PUSH; $i++) {
      $chunkId = uniqid('', true);
      $chunkName = "c" . $i . "_" . $fileName;
      $db->registerChunk($chunkId, $chunkName, $chunkSize);
      $db->registerChunksFile($chunkId, $fileId);

      $operationId = uniqid('', true);
      $db->registerOperation($operationId, $userId, $fileId, $chunkId, $nodes[$i - 1]->id, 'w');

      $result[$i] = $nodes[$i - 1]->url . 'upload.php?file=c/' . $chunkId . '&operationId=' . $operationId;
    }
  }

  return $result;
}


function uploadChunks($userId, $fileId, $fileName, $fileSize, $nodesRequired)
{
  //global $db;
  $db = new DbHandler();
  $chunkSize = intval(ceil($fileSize / $nodesRequired));
  //obtener nodos
  $nodes = $db->getNodesActive();
  $nodesTotal = count($nodes);
  $result = false;

  for ($i = 1; $i <= $nodesRequired; $i++) {

    $r1 = rand(0, $nodesTotal - 1);
    $r2 = rand(0, $nodesTotal - 1);
    if ($nodes[$r1]->used > $nodes[$r2]->used) {
      $d = $r1;
    } else {
      $d = $r2;
    }

    $nodes[$d]->setUf($chunkSize);
    $chunkId = uniqid('', true);
    $chunkName = $fileName . "." . $i;
    $db->registerChunk($chunkId, $chunkName, $chunkSize);
    $db->registerChunksFile($chunkId, $fileId);

    $operationId = uniqid('', true);
    $db->registerOperation($operationId, $userId, $fileId, $chunkId, $nodes[$d]->id, 'w');

    $result[$i] = $nodes[$d]->url . 'upload.php?file=c/' . $chunkId . '&operationId=' . $operationId;
  }

  return $result;
}

/**
 * [download description]
 * @param  [type] $userId    [description]
 * @param  [type] $fileId    [description]
 * @param  [type] $chunkId   [description]
 * @param  [type] $chunkSize [description]
 * @return [type]            [description]
 */
function downloadChunks($userId, $fileId, $chunkId, $chunkSize)
{
  //global $db;
  $db = new DbHandler();
  //obtener nodos
  $chunks = $db->getInfoChunksFile($fileId);
  $totalChunks = count($chunks);
  $nodes = $db->getChunkInNodes($fileId);
  $nodesTotal = count($nodes);
  $result = false;

  for ($i = 0; $i < $totalChunks; $i++) {
    $operationId = uniqid('', true);
    $db->registerOperation($operationId, $userId, $fileId, $chunkId, $nodes[$i]->id, 'r');
    $result[$i] = $chunks[$i]["url"] . 'c/' . $chunks[$i]["id"];
  }

  return $result;
}

/**
 * [download description]
 * @param  [type] $userId    [description]
 * @param  [type] $fileId    [description]
 * @param  [type] $chunkId   [description]
 * @param  [type] $chunkSize [description]
 * @return [type]            [description]
 */
function downloadChunksReliability($userId, $fileId, $chunkId, $chunkSize)
{
  $db = new DbHandler();
  $result = false;
  $chunks = $db->getInfoChunksFile($fileId);
  $totalChunks = count($chunks);
  $nodes = $db->getChunkInNodesReliability($fileId);
  $nodesTotal = count($nodes);
  $result = false;

  sortNodesByUF($nodes, $nodesTotal, $chunks[0]['size']);

  #print_r($chunks);

  $i = 0;
  $j = 0;
  while($j < NODES_REQUIRED_PULL && $i < $nodesTotal){
    $chunkId = $db->getChunkInNode($nodes[$i]->id, $fileId);
    $operationId = uniqid('', true);
    $db->registerOperation($operationId, $userId, $fileId, $chunkId, $nodes[$i]->id, 'r');
    $db->updateOperation($operationId);
    //print_r($nodes[$i]);
    $key = array_search($nodes[$i]->url, array_column($chunks, 'url'));
    $host = $chunks[$key]["url"];
    //print_r($chunks[$key]);
    if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
      $result[$i] = $chunks[$key]["url"] . 'c/' . $chunks[$key]["id"];
      $j++;
      fclose($socket);
    } else {
      #echo 'offline.';
    }


    #$file_headers = @get_headers($chunks[$key]["url"]);
    #echo $chunks[$key]["url"] . 'c/' . $chunks[$key]["id"];
    #print_r($file_headers);
    #if ($file_headers || (isset($file_headers[0]) && $file_headers[0] != 'HTTP/1.1 404 Not Found')) {
    #  $result[$i] = $chunks[$key]["url"] . 'c/' . $chunks[$key]["id"];
    #  $j++;
    #}
    $i++;
    //$operations[] = $operationId;
  }
  
  return $result;
}



/**
 * [upload description]
 * @param  [type] $userId    [description]
 * @param  [type] $fileId    [description]
 * @param  [type] $chunkId   [description]
 * @param  [type] $chunkSize [description]
 * @return [type]            [description]
 */
function upload($userId, $userImpactFactor, $fileId, $fileName, $chunks, $chunkSize)
{
  //global $db;
  $db = new DbHandler();
  //obtener nodos
  $nodes = $db->getNodesActive();
  $nodesTotal = count($nodes);
  $result = false;

  if ($chunks == 1) {
    $chunkId = uniqid('', true);
    $chunkName = $fileName . 1;
    $db->registerChunk($chunkId, $chunkName, $chunkSize);
    $db->registerChunksFile($chunkId, $fileId);
  }

  if ($nodesTotal > 0) {
    $replicationFactor = round($nodesTotal * $userImpactFactor);
    sortNodesByUF($nodes, $nodesTotal, $chunkSize);

    $operationId = uniqid('', true);
    $db->registerOperation($operationId, $userId, $fileId, $chunkId, $nodes[0]->id, 'w');
    $result['url'] = $nodes[0]->url . 'upload.php?file=c/' . $fileId . '&operationId=' . $operationId;

    for ($i = 1; $i < $replicationFactor; $i++) {
      $operationId = uniqid('', true);
      $db->registerOperation($operationId, $userId, $fileId, $chunkId, $nodes[$i]->id, 'w');
    }
  }
  return $result;
}

/**
 * [download description]
 * @param  [type] $userId    [description]
 * @param  [type] $fileId    [description]
 * @param  [type] $chunkId   [description]
 * @param  [type] $chunkSize [description]
 * @return [type]            [description]
 */
function download($userId, $fileId, $chunkSize)
{
  //global $db;
  $db = new DbHandler();
  //obtener nodos
  $nodes = $db->getChunkInNodes($fileId);
  //print_r($nodes);
  $nodesTotal = count($nodes);
  $result = false;

  if ($nodesTotal > 0) {
    //sortNodesByUF($nodes, $nodesTotal, $chunkSize);

    $operationId = uniqid('', true);
    $db->registerOperation($operationId, $userId, $fileId, $chunkId, $nodes[0]->id, 'r');
    $db->updateOperation($operationId);
    $result = $nodes[0]->url . 'c/' . $fileId;
    //$result['operationId'] = $operationId;
  }
  return $result;
}

/**
 * SORT NODES BY UF
 * @param  [type] &$nodes      Nodes
 * @param  [type] &$nodesTotal Nodes total
 * @param  [type] $chunkSize   Chunk size
 * @return Node Object         [description]
 */
function sortNodesByUF(&$nodes, &$nodesTotal, $chunkSize)
{
  for ($i = 0; $i < $nodesTotal; $i++) {
    $nodes[$i]->setUf($chunkSize);
  }
  usort($nodes, array('Node', 'compare'));
}
