<?php

namespace app\models;

trait HasOpenHoursTrait
{
    /**
     * @return HasOpenHoursInterface|null
     */
    public function getParent(): ?HasOpenHoursInterface
    {
        if ($this->hasParent()) {
            $parent_class = '\app\models\\' . $this->getParentType();
            if ($parent = $parent_class::findOne(['id' => $this->getParentId()])) {
                return $parent;
            }
        }
        return null;
    }

    /**
     * Returns open hours of a specific day (e.g. Wed, Thu, ...)
     *
     * @param string $week_day
     * @return array|null
     */
    public function getWeekDayOpenHours(string $week_day): ?array
    {
        return OpenHours::find()
            ->where(['entity_id' => $this->id, 'entity_type' => $this->getEntityType(), 'week_day' => $week_day])
            ->orderBy('from ASC')
            ->all();
    }

    /**
     * Returns the open hour object that the datetime is inside or it returns null if didn't find any
     *
     * @param \DateTime $datetime
     * @return OpenHours|null
     */
    public function getDateTimeOpenHour(\DateTime $datetime): ?OpenHours
    {
        $time = $datetime->format("H:i:s");
        return OpenHours::find()
            ->where(['entity_id' => $this->id, 'entity_type' => $this->getEntityType(), 'week_day' => $datetime->format('D')])
            ->andWhere("'$time' BETWEEN open_hours.from AND open_hours.to")
            ->one();
    }

    /**
     *  Returns the exception (app/model/Exceptions) object that the datetime is affected by or it returns null if didn't find any
     *
     * @param \DateTime $datetime
     * @return Exceptions|null
     */
    public function getDateTimeException(\DateTime $datetime): ?Exceptions
    {
        $formated_datetime = $datetime->format("Y-m-d H:i:s");
        return Exceptions::find()
            ->where(['entity_id' => $this->id, 'entity_type' => $this->getEntityType()])
            ->andWhere("'$formated_datetime' BETWEEN exceptions.from AND exceptions.to")
            ->one();
    }

    /**
     * @param \DateTime $start
     * @return array|null
     */
    public function getAllExceptionsFrom(\DateTime $start): ?array
    {
        $formated_datetime = $start->format("Y-m-d H:i:s");
        return Exceptions::find()
            ->where(['entity_id' => $this->id, 'entity_type' => $this->getEntityType()])
            ->andWhere("exceptions.from >= '$formated_datetime' OR exceptions.to >= '$formated_datetime'")
            ->orderBy('from ASC')
            ->all();
    }

    /**
     * @return string|null
     */
    private function getEntityType()
    {
        try {
            return (new \ReflectionClass(get_class($this)))->getShortName();
        } catch (\Exception $e) {
            return null;
        }
    }
}
