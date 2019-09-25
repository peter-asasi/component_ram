<?php
namespace by\component\ram;


class ByStatement
{
    private $action;
    private $resource;
    private $effect;
    private $condition;
    private $isSatisfy;

    public function __construct($data)
    {
        $this->setAction(array_key_exists('Action', $data) ? $data['Action'] : '');
        $this->setResource(array_key_exists('Resource', $data) ? $data['Resource'] : '');
        $this->setEffect(array_key_exists('Effect', $data) ? $data['Effect'] : '');
        $this->setCondition(array_key_exists('Condition', $data) ? $data['Condition'] : '');
        $this->setIsSatisfy(false);
    }

    /**
     * @return mixed
     */
    public function getIsSatisfy()
    {
        return $this->isSatisfy;
    }

    /**
     * @param mixed $isSatisfy
     */
    public function setIsSatisfy($isSatisfy): void
    {
        $this->isSatisfy = $isSatisfy;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action): void
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $resource
     */
    public function setResource($resource): void
    {
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function getEffect()
    {
        return $this->effect;
    }

    /**
     * @param mixed $effect
     */
    public function setEffect($effect): void
    {
        $this->effect = $effect;
    }

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param mixed $condition
     */
    public function setCondition($condition): void
    {
        $this->condition = $condition;
    }
}
