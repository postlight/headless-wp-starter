module Asset exposing (Image, crowdSourcePartner, enterpriseRegisterImage, fb, flightMap, hamburger, jpEnterpriseRegisterImage, logo, mail, src, talentMatch, topImage, whiteLogo)

{-| Assets, such as images, videos, and audio. (We only have images for now.)

We should never expose asset URLs directly; this module should be in charge of
all of them. One source of truth!

-}

import Html exposing (Attribute, Html)
import Html.Attributes as Attr


type Image
    = Image String



-- Images


fb : Image
fb =
    image "fb.svg"


hamburger : Image
hamburger =
    image "hamburger.svg"


logo : Image
logo =
    image "logo.svg"


whiteLogo : Image
whiteLogo =
    image "white-logo.svg"


enterpriseRegisterImage : Image
enterpriseRegisterImage =
    image "register-illustration.svg"


jpEnterpriseRegisterImage : Image
jpEnterpriseRegisterImage =
    image "jp-register-illustration.svg"


flightMap : Image
flightMap =
    image "flight-map.svg"


talentMatch : Image
talentMatch =
    image "talent-match.svg"


crowdSourcePartner : Image
crowdSourcePartner =
    image "japan-crd-service.svg"


serviceCrowdSourcing : Image
serviceCrowdSourcing =
    image "service-crdsourcing.svg"


serviceEc : Image
serviceEc =
    image "service-ec.svg"


serviceExhibition : Image
serviceExhibition =
    image "service-exhibition.svg"


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
