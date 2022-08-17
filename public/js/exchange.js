function showExchanges()
{
        $exchangeArea = $("div#test");
        $exchangeArea.toggleClass("hidden");
        $exchangeArea.toggleClass("exchange");
        if ($exchangeArea.hasClass("hidden")){
                $("div#exchangeButton").text("Verrechnete Transaktionen:");
        }else{
                $("div#exchangeButton").text("Einfahren");
        }
}