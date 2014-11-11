<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\DependencyResolver;

/**
 * @author Nils Adermann <naderman@naderman.de>
 */
class DebugSolver extends Solver
{
    protected function printDecisionMap()
    {
        echo "\nDecisionMap: \n";
        foreach ($this->decisionMap as $packageId => $level) {
            if ($packageId === 0) {
                continue;
            }
            echo $this->getDebugBullet($level) . $this->pool->packageById($packageId)."\n";
        }
        echo "\n";
    }
    
    private function getDebugBullet($level)
    {
        if ($level === 0) {
            return '    ?';
        } 

        return $level < 0 ? '    -' : '    +';
    }

    protected function printDecisionQueue()
    {
        echo "DecisionQueue: \n";
        foreach ($this->decisionQueue as $i => $literal) {
            echo '    ' . $this->pool->literalToString($literal) . ' ' . $this->decisionQueueWhy[$i]." level ".$this->decisionMap[abs($literal)]."\n";
        }
        echo "\n";
    }

    protected function printWatches()
    {
        echo "\nWatches:\n";
        foreach ($this->watches as $literalId => $watch) {
            $this->printWatch($watch, $literalId);
        }
    }
    
    private function printWatch($watch, $literalId)
    {
        echo '  '.$this->literalFromId($literalId)."\n";
        $queue = array(array('    ', $watch));

        while (!empty($queue)) {
            $this->printWatchQueue($queue);
        }

        echo "\n";     
    }
    
    private function printWatchQueue($queue)
    {
        list($indent, $watch) = array_pop($queue);

        echo $indent . $watch;

        if ( ! $watch) {
            echo "\n";
            return;
        }
        
        echo ' [id=' . $watch->getId() . ',watch1=' . $this->literalFromId($watch->watch1) . ',watch2=' . $this->literalFromId($watch->watch2) . "]"."\n";

        if ($watch->next1 == $watch) {
            echo $indent . "    1 *RECURSION*";
        }
        if ($watch->next2 == $watch) {
            echo $indent . "    2 *RECURSION*";
        }
        
        if ( ! ($watch->next1 || $watch->next2)) {
            return;
        }
        $indent = str_replace(array('1', '2'), ' ', $indent);

        array_push($queue, array($indent . '    2 ', $watch->next2));
        array_push($queue, array($indent . '    1 ', $watch->next1));        
    }
}
