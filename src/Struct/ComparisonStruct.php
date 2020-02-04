<?php

namespace App\Struct;

class ComparisonStruct
{
    private $subject;
    private $a;
    private $b;
    private $positionA;
    private $positionB;

    public function __construct($subject, $a, $b)
    {
        $this->subject = $subject;
        $this->a = $a;
        $this->b = $b;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    public function getA()
    {
        return $this->a;
    }

    public function setA($a): void
    {
        $this->a = $a;
    }

    public function getB()
    {
        return $this->b;
    }

    public function setB($b): void
    {
        $this->b = $b;
    }

    public function getPositionA()
    {
        return $this->positionA;
    }

    public function setPositionA($positionA): void
    {
        $this->positionA = $positionA;
    }

    public function getPositionB()
    {
        return $this->positionB;
    }

    public function setPositionB($positionB): void
    {
        $this->positionB = $positionB;
    }
}