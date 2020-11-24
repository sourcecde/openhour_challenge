<?php

namespace app\models;

interface HasOpenHoursInterface
{
    /**
     * @return bool
     */
    public function hasParent(): bool;

    /**
     * @return string|null
     */
    public function getParentType(): ?string;

    /**
     * @return int|null
     */
    public function getParentId(): ?int;

    /**
     * @return HasOpenHoursInterface|null
     */
    public function getParent(): ?HasOpenHoursInterface;

    /**
     * Returns open hours of a specific day (e.g. Wed, Thu, ...)
     *
     * @param string $week_day
     * @return array|null
     */
    public function getWeekDayOpenHours(string $week_day): ?array;

    /**
     * Returns the open hour object that the datetime is inside or it returns null if didn't find any
     *
     * @param \DateTime $datetime
     * @return OpenHours|null
     */
    public function getDateTimeOpenHour(\DateTime $datetime): ?OpenHours;

    /**
     *  Returns the exception (app/model/Exceptions) object that the datetime is affected by or it returns null if didn't find any
     *
     * @param \DateTime $datetime
     * @return Exceptions|null
     */
    public function getDateTimeException(\DateTime $datetime): ?Exceptions;

    /**
     * @param \DateTime $start
     * @return array|null
     */
    public function getAllExceptionsFrom(\DateTime $start): ?array;
}
