<?php
/*
* TwoChoices
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

      $twoChoices = twoChoices($nodes, $nodesTotal, $chunkSize);//primer nodo ancla
      $operationId = uniqid('', true);
      $db->registerOperation($operationId, $userId, $fileId, $chunkId, $twoChoices->id, 'w');
      $result['url'] = $twoChoices->url . '?operationId=' . $operationId;

   	for ($i = 1; $i < $replicationFactor; $i++) { 
         $twoChoices = twoChoices($nodes, $nodesTotal, $chunkSize);
         $operationId = uniqid('', true);
         $db->registerOperation($operationId, $userId, $fileId, $chunkId, $twoChoices->id, 'w');
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
      $twoChoices = twoChoices($nodes, $nodesTotal, $chunkSize);
      $operationId = uniqid('', true);
      $db->registerOperation($operationId, $userId, $fileId, $chunkId, $twoChoices->id, 'r');
      $result['url'] = $twoChoices->url . 'chunks/' . $chunkId;
      $result['operationId'] = $operationId;
   }
   return $result;
}

/**
 * TWO CHOICES
 * @param  [type] &$nodes      Nodes
 * @param  [type] &$nodesTotal Nodes total
 * @param  [type] $chunkSize   Chunk size
 * @return Node Object         [description]
 */
function twoChoices(&$nodes, &$nodesTotal, $chunkSize) {
   $rand1 = mt_rand(0, $nodesTotal - 1);
   $rand2 = mt_rand(0, $nodesTotal - 1);
   $node1 = $nodes[$rand1];
   $node1->setUf($chunkSize);
   $node2 = $nodes[$rand2];
   $node2->setUf($chunkSize);
   $compare = Node::compare($node1, $node2);
   if ($compare == -1) {
      array_splice($nodes, $rand1, 1);
      $twoChoices = $node1;
   } else {
      array_splice($nodes, $rand2, 1);
      $twoChoices = $node2;
   }
   $nodesTotal--;
   return $twoChoices;
}
?>