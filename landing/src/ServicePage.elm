module ServicePage exposing (..)

import Element exposing (..)
import Element.Background as Background
import Element.Border as Border
import Element.Font as Font
import Html exposing (Html)
import I18Next
    exposing
        ( Translations
        , t
        )


view : Translations -> Html msg
view translations =
    layout [] <|
        column [ width fill, paddingXY 0 180, spacingXY 0 100 ]
            [ row [ width <| px 960, spaceEvenly, centerX ]
                [ column []
                    [ paragraph [ Font.size 36, Font.bold, paddingXY 0 16 ]
                        [ el [ alignLeft ] <| text ("1. " ++ t translations "servicePage.content.1.title.japan")
                        , el [ alignLeft, Font.color <| rgb255 217 74 61 ] <| text (t translations "servicePage.content.1.title.crowdSource")
                        , el [ alignLeft ] <| text (t translations "servicePage.content.1.title.execute")
                        ]
                    , paragraph
                        [ width <| px 414
                        , paddingEach { top = 0, left = 0, right = 0, bottom = 24 }
                        , alignLeft
                        , spacing 10
                        , Font.size 18
                        , Font.color <| rgb255 99 99 99
                        , Font.alignLeft
                        ]
                        [ text (t translations "servicePagcontent.1.description")
                        ]
                    , image []
                        { src = "%PUBLIC_URL%/assets/images/japan-crd-service.svg"
                        , description = "crowd sourcing partners"
                        }
                    ]
                , column
                    []
                    [ image [ width <| px 312, height <| px 246 ]
                        { src = "%PUBLIC_URL%/assets/images/service-crdsourcing.svg"
                        , description = "crowd sourcing partners"
                        }
                    ]
                ]
            , row [ width <| px 960, spaceEvenly, centerX ]
                [ column
                    []
                    [ image [ width <| px 370, height <| px 195 ]
                        { src = "%PUBLIC_URL%/assets/images/service-ec.svg"
                        , description = "crowd sourcing partners"
                        }
                    ]
                , column []
                    [ paragraph [ Font.size 36, Font.bold, paddingXY 0 16 ]
                        [ el [ alignLeft ] <| text ("2. " ++ t translations "servicePage.content.2.title.amazon")
                        , el [ alignLeft, Font.color <| rgb255 217 74 61 ] <| text (t translations "servicePage.content.2.title.eCommerce")
                        ]
                    , paragraph
                        [ width <| px 455
                        , paddingEach { top = 0, left = 0, right = 0, bottom = 64 }
                        , spacing 10
                        , Font.size 18
                        , Font.color <| rgb255 99 99 99
                        , Font.alignLeft
                        ]
                        [ text (t translations "servicePage.content.2.description")
                        ]
                    , row [ centerY, spacing 18 ]
                        [ image []
                            { src = "%PUBLIC_URL%/assets/images/logo-amazon.svg"
                            , description = "amazon"
                            }
                        , image []
                            { src = "%PUBLIC_URL%/assets/images/logo-rakuten.svg"
                            , description = "rakuten"
                            }
                        ]
                    ]
                ]
            , row [ width <| px 960, spaceEvenly, centerX, paddingEach { top = 0, left = 0, right = 0, bottom = 100 } ]
                [ column [ width <| px 450 ]
                    [ paragraph [ Font.size 36, Font.bold, paddingXY 0 16 ]
                        [ el [ alignLeft ] <| text ("3. " ++ t translations "servicePage.content.3.title.japan")
                        , el [ alignLeft, Font.color <| rgb255 217 74 61 ] <| text (t translations "servicePage.content.3.title.brand")
                        , el [ alignLeft ] <| text (t translations "servicePage.content.3.title.manage")
                        ]
                    , paragraph
                        [ width <| px 387
                        , spacing 10
                        , Font.size 18
                        , Font.color <| rgb255 99 99 99
                        , Font.alignLeft
                        ]
                        [ text (t translations "servicePage.content.3.description")
                        ]
                    ]
                , column
                    []
                    [ image [ width <| px 263, height <| px 211 ]
                        { src = "%PUBLIC_URL%/assets/images/service-exhibition.svg"
                        , description = "exhibition service"
                        }
                    ]
                ]
            , row [ width fill, centerX, Background.color <| rgb255 255 246 244 ]
                [ column [ centerX, spacing 24, padding 40 ]
                    [ el [ Font.bold, Font.size 20, centerX, paddingXY 0 20 ] <| text (t translations "servicePage.detail.title")
                    , row [ spacing 24 ]
                        [ column [ Background.color <| rgb255 255 255 255, width <| px 469, height <| px 173 ]
                            [ paragraph [ paddingEach { top = 34, right = 24, bottom = 24, left = 24 }, Font.alignLeft ]
                                [ el [ Font.bold ] <| text (t translations "servicePage.detail.content.1.stressTitle")
                                , text (t translations "servicePage.detail.content.1.title")
                                ]
                            , paragraph [ Font.color <| rgb255 99 99 99, paddingEach { top = 0, right = 24, bottom = 0, left = 24 }, Font.alignLeft, Font.size 16 ] [ text (t translations "servicePage.detail.content.1.description") ]
                            ]
                        , column [ Background.color <| rgb255 255 255 255, width <| px 469, height <| px 173 ]
                            [ paragraph [ paddingEach { top = 34, right = 24, bottom = 24, left = 24 }, Font.alignLeft ]
                                [ el [ Font.bold ] <| text (t translations "servicePage.detail.content.2.stressTitle")
                                , text (t translations "servicePage.detail.content.2.title")
                                ]
                            , paragraph [ Font.color <| rgb255 99 99 99, paddingEach { top = 0, right = 24, bottom = 0, left = 24 }, Font.alignLeft, Font.size 16 ] [ text (t translations "servicePage.detail.content.2.description") ]
                            ]
                        ]
                    , row [ spacing 24 ]
                        [ column [ Background.color <| rgb255 255 255 255, width <| px 469, height <| px 173 ]
                            [ paragraph [ paddingEach { top = 34, right = 24, bottom = 24, left = 24 }, Font.alignLeft ]
                                [ el [ Font.bold ] <| text (t translations "servicePage.detail.content.3.stressTitle")
                                , text (t translations "servicePage.detail.content.3.title")
                                ]
                            , paragraph [ Font.color <| rgb255 99 99 99, paddingEach { top = 0, right = 24, bottom = 0, left = 24 }, Font.alignLeft, Font.size 16 ] [ text (t translations "servicePage.detail.content.3.description") ]
                            ]
                        , column [ Background.color <| rgb255 255 255 255, width <| px 469, height <| px 173 ]
                            [ paragraph [ paddingEach { top = 34, right = 24, bottom = 24, left = 24 }, Font.alignLeft ]
                                [ el [ Font.bold ] <| text (t translations "servicePage.detail.content.4.stressTitle")
                                , text (t translations "servicePage.detail.content.4.title")
                                ]
                            , paragraph [ Font.color <| rgb255 99 99 99, paddingEach { top = 0, right = 24, bottom = 0, left = 24 }, Font.alignLeft, Font.size 16 ] [ text (t translations "servicePage.detail.content.4.description") ]
                            ]
                        ]
                    ]
                ]
            , row [ width fill ]
                [ column [ spacing 18, width (fill |> maximum 552), centerX ]
                    [ paragraph [ width fill, Font.size 36, Font.bold, Font.color <| rgb255 1 31 38 ] [ text (t translations "servicePage.consult.fromCrowdSource") ]
                    , paragraph
                        [ Font.size 36
                        , Font.bold
                        , Font.center
                        , Font.color <| rgb255 1 31 38
                        , paddingEach { top = 0, bottom = 32, right = 0, left = 0 }
                        ]
                        [ text (t translations "servicePage.consult.openJapanMarket")
                        ]
                    , link
                        [ height <| px 48
                        , paddingXY 15 5
                        , Background.color <| rgb255 217 74 61
                        , Border.rounded 54
                        , Font.color <| rgb255 255 255 255
                        , centerX
                        ]
                        { url = "https://gumo.works/bd"
                        , label = el [ centerX ] <| text (t translations "top.freeConsult")
                        }
                    ]
                , column [ centerX ]
                    [ image [ width <| px 300, height <| px 235, Font.size 16 ]
                        { src = "%PUBLIC_URL%/assets/images/earth.svg"
                        , description = "consulting service"
                        }
                    ]
                ]
            ]
