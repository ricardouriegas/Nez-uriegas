<?php
/*
* Utilization Factor(uf)
* Author: Pablo Morales Ferreira
* Company: Cinvestav-Tamaulipas
*/

/**
* [upload description]
* @param  [type] $userId           [description]
* @param  [type] $userImpactFactor [description]
* @param  [type] $fileId           [description]
* @param  [type] $chunkId          [description]
* @param  [type] $chunkSize        [description]
* @return [type]                   [description]
*/
function upload($userId, $userImpactFactor, $fileId, $chunkId, $chunkSize) {
  global $db;
  //obtener nodos
  $nodes = $db->getNodesActive();
  $nodesTotal = count($nodes);
  $result = false;

  if ($nodesTotal > 0) {
     $replicationFactor = round($nodesTotal * $userImpactFactor);
     sortNodesByUF($nodes, $nodesTotal, $chunkSize);

     $operationId = uniqid('', true);
     $db->registerOperation($operationId, $userId, $fileId, $chunkId, $nodes[0]->id, 'w');
     $result['url'] = $nodes[0]->url . '?operationId=' . $operationId;

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
function download($userId, $fileId, $chunkId, $chunkSize) {
  global $db;
  //obtener nodos
  $nodes = $db->getChunkInNodes($chunkId);
  $nodesTotal = count($nodes);
  $result = false;

  if ($nodesTotal > 0) {
     sortNodesByUF($nodes, $nodesTotal, $chunkSize);

     $operationId = uniqid('', true);
     $db->registerOperation($operationId, $userId, $fileId, $chunkId, $nodes[0]->id, 'r');
     $result['url'] = $nodes[0]->url . 'chunks/' . $chunkId;
     $result['operationId'] = $operationId;
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
function sortNodesByUF(&$nodes, &$nodesTotal, $chunkSize) {
  for ($i=0; $i < $nodesTotal; $i++) {
     $nodes[$i]->setUf($chunkSize);
  }
  usort($nodes, array('Node','compare'));
}
?>