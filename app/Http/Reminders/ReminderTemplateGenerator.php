<?php

declare(strict_types=1);

namespace App\Http\Reminders;

use App\Models\LaunchTime;
use App\Models\Pad;
use App\Models\Provider;
use App\Models\Reminder;
use App\Models\Rocket;

class ReminderTemplateGenerator
{

    private const TEMPLATE = '<div class="reminder wrapper" style="background-color: #3F69D4; display: flex; padding: 30px;">
        <div class="content" style="max-width: 700px; padding: 30px 60px; background-color: #FFFFFF; border-radius: 10px; display: flex; flex-direction: column; margin: auto;">
            <div class="header" style="color: #787984; text-align: center; font-size: 26px; font-weight: bold; margin-bottom: 50px;">
                <span>LAUNCH REMINDER</span>
            </div>
            <img src="%ROCKETURL%" alt="%ROCKETNAME%" style="width: 250px; height: 250px; background-color: #F1F4F8; border-radius: 10px; margin: 0 auto;">
            <span class="launch-name" style="color: #232638; font-weight: bold; font-size: 40px; text-align: center; margin: 20px 0 10px 0;">%LAUNCHNAME%</span>
            <span class="minor" style="color: #787984; font-weight: 500; font-size: 15px; text-align: center; margin-bottom: 50px;">will launch today at</span>
            <div class="launch-time" style="border-radius: 10px; background-color: #F1F4F8; color: #232638; text-align: center; padding: 10px 40px; font-weight: bold; font-size: 35px;">
                %LAUNCH_TIME%
            </div>
            <span class="launch-description" style="max-width: 100%; text-align: justify; margin: 50px auto; font-size: 22px; font-weight: 500; color: #232638;">%DESCRIPTION%</span>
            <div class="launch-item" style="width: 100%; display: flex; flex-direction: column; margin-bottom: 25px;">
                <span class="item-name" style="color: #232638; text-transform: uppercase; font-weight: bold; font-size: 18px; margin-bottom: 10px;">PROVIDER</span>
                <div class="item-wrapper" style="display: flex; height: 80px; background-color: #f1f4f8; border-radius: 10px;">
                    <img src="%PROVIDERURL%" alt="%PROVIDERNAME%" style="height: 80px; width: 80px; background-color: #f1f4f8; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                    <span style="margin: auto 50px; color: #232638; font-weight: bold; font-size: 22px;">%PROVIDERNAME%</span>
                </div>
            </div>
            <div class="launch-item" style="width: 100%; display: flex; flex-direction: column; margin-bottom: 25px;">
                <span class="item-name" style="color: #232638; text-transform: uppercase; font-weight: bold; font-size: 18px; margin-bottom: 10px;">ROCKET</span>
                <div class="item-wrapper" style="display: flex; height: 80px; background-color: #F1F4F8; border-radius: 10px;">
                    <img src="%ROCKETURL%" alt="%ROCKETNAME%" style="height: 80px; width: 80px; background-color: #f1f4f8; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                    <span style="margin: auto 50px; color: #232638; font-weight: bold; font-size: 22px;">%ROCKETNAME%</span>
                </div>
            </div>
            <div class="launch-item" style="width: 100%; display: flex; flex-direction: column; margin-bottom: 25px;">
                <span class="item-name" style="color: #232638; text-transform: uppercase; font-weight: bold; font-size: 18px; margin-bottom: 10px;">PAD</span>
                <div class="item-wrapper" style="display: flex; height: 80px; background-color: #F1F4F8; border-radius: 10px;">
                    <img src="%PADURL%" alt="%PADNAME%" style="height: 80px; width: 80px; background-color: #f1f4f8; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                    <span style="margin: auto 50px; color: #232638; font-weight: bold; font-size: 22px;">%PADNAME%</span>
                </div>
            </div>
        </div>
    </div>';

    /**
     * @param Reminder $reminder
     */
    public static function generate(Reminder $reminder): void
    {
        $launch = $reminder->getLaunch();

        $mailHtml = self::TEMPLATE;
        $mailHtml = str_replace(
            [
                '%LAUNCHNAME%',
                '%DESCRIPTION%',
                '%LAUNCH_TIME%',
                '%PROVIDERNAME%',
                '%PROVIDERURL%',
                '%ROCKETNAME%',
                '%ROCKETURL%',
                '%PADNAME%',
                '%PADURL%',
            ],
            [
                $launch->getName(),
                $launch->getDescription(),
                self::getLaunchTimeFormatted($launch->getLaunchTime()),
                self::getProviderName($launch->getProvider()),
                self::getProviderImageURL($launch->getProvider()),
                self::getRocketName($launch->getRocket()),
                self::getRocketImageURL($launch->getRocket()),
                self::getPadName($launch->getPad()),
                self::getPadImageURL($launch->getPad()),
            ],
            $mailHtml
        );

        $header  = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: reminder@jakobi.io\r\n";
        $header .= "X-Mailer: PHP " . phpversion();

        mail(
            $reminder->getUser()->getEmail(),
            $reminder->getTitle(),
            $mailHtml,
            $header
        );
    }

    /**
     * @param Provider|null $provider
     * @return string
     */
    private static function getProviderImageURL(?Provider $provider): string
    {
        if ($provider === null) {
            return 'NaN';
        }

        return $provider->getImageURL() ?? 'NaN';
    }

    /**
     * @param Provider|null $provider
     * @return string
     */
    private static function getProviderName(?Provider $provider): string
    {
        if ($provider === null) {
            return 'NaN';
        }

        return $provider->getName() ?? 'NaN';
    }

    /**
     * @param Rocket|null $rocket
     * @return string
     */
    private static function getRocketImageURL(?Rocket $rocket): string
    {
        if ($rocket === null) {
            return 'NaN';
        }

        return $rocket->getImageURL() ?? 'NaN';
    }

    /**
     * @param Rocket|null $rocket
     * @return string
     */
    private static function getRocketName(?Rocket $rocket): string
    {
        if ($rocket === null) {
            return 'NaN';
        }

        return $rocket->getName() ?? 'NaN';
    }

    /**
     * @param Pad|null $pad
     * @return string
     */
    private static function getPadImageURL(?Pad $pad): string
    {
        if ($pad === null) {
            return 'NaN';
        }

        return $pad->getImageURL() ?? 'NaN';
    }

    /**
     * @param Pad|null $pad
     * @return string
     */
    private static function getPadName(?Pad $pad): string
    {
        if ($pad === null) {
            return 'NaN';
        }

        return $pad->getName() ?? 'NaN';
    }

    /**
     * @param LaunchTime|null $launchTime
     * @return string
     */
    private static function getLaunchTimeFormatted(?LaunchTime $launchTime): string
    {
        if ($launchTime === null) {
            return 'NaN';
        }

        $launchNet = $launchTime->getLaunchNet();

        if ($launchNet === null) {
            return 'NaN';
        }

        return $launchNet->format("H:i e");
    }
}
