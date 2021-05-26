<?php

namespace App;

class Mails
{
    public static function renderNotification(string $type, array $data): string
    {
        return ("
             <div>
            <h1>BlocBeta</h1>
            <p>
            poop
            </p>
            
        </div>
        ");
    }

    public static function renderReservationConfirmation(array $data): string
    {
        return ("
        <div>
            <h1>BlocBeta</h1>
            <p>
            Your Time Slot reservation @{$data["locationName"]} on {$data["reservationDate"]} from {$data["startTime"]} to {$data["endTime"]}
            </p>
            
            <br/>
            <br/>
            
            <p>
            If you cannot attend to your slot, please <a href='{$data["cancellationLink"]}'>cancel it!</a>
            </p>
        </div>
        ");
    }
}
