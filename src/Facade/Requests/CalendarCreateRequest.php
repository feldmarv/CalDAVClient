<?php namespace CalDAVClient\Facade\Requests;
/**
 * Copyright 2017 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use CalDAVClient\Facade\Utils\ICalTimeZoneBuilder;
use Sabre\Xml\Element\Cdata;
use Sabre\Xml\Service;

/**
 * Class CalendarCreateRequest
 * @package CalDAVClient\Facade\Requests
 */
final class CalendarCreateRequest implements IAbstractWebDAVRequest
{
    /**
     * @var MakeCalendarRequestDTO
     */
    private $dto;

    /**
     * CalendarCreateRequest constructor.
     * @param MakeCalendarRequestDTO $dto
     */
    public function __construct(MakeCalendarRequestDTO $dto)
    {
        $this->dto = $dto;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $service = new Service();
        $service->namespaceMap = [
            'DAV:'                          => 'D',
            'urn:ietf:params:xml:ns:caldav' => 'C',
        ];

        $props = [
            [
                'name'       =>'{DAV:}displayname',
                'attributes' => ['xml:lang' => 'eng'],
                'value'      =>  $this->dto->getDisplayName(),
            ],

            '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => [
                [
                    'name'        => '{urn:ietf:params:xml:ns:caldav}comp',
                    'attributes'  => ['name' => 'VEVENT']
                ]
            ],
            '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => new Cdata
            (
                ICalTimeZoneBuilder::build
                (
                    $this->dto->getTimezone(),
                    $this->dto->getDisplayName()
                )->render()
            )
        ];

        if(!empty($this->dto->getDescription()))
            $props['{urn:ietf:params:xml:ns:caldav}calendar-description'] = $this->dto->getDescription();

        return $service->write('{urn:ietf:params:xml:ns:caldav}mkcalendar',
            [
                '{DAV:}set'  => [
                    '{DAV:}prop'  => [
                        $props
                    ]
                ]
            ]);
    }
}