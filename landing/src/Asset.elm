module Asset exposing (Image, hamburger, logo, mail, src, topImage)

{-| Assets, such as images, videos, and audio. (We only have images for now.)

We should never expose asset URLs directly; this module should be in charge of
all of them. One source of truth!

-}

import Html exposing (Attribute, Html)
import Html.Attributes as Attr


type Image
    = Image String



-- Images


hamburger : Image
hamburger =
    image "hamburger.svg"


logo : Image
logo =
    image "logo.svg"


mail : Image
mail =
    image "mail.svg"


topImage : Int -> String
topImage index =
    "%PUBLIC_URL%/assets/images/top-" ++ String.fromInt index ++ ".jpg"


image : String -> Image
image filename =
    Image ("%PUBLIC_URL%/assets/images/" ++ filename)



-- Using Images


src : Image -> Attribute msg
src (Image url) =
    Attr.src url
