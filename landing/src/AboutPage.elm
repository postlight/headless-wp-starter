module AboutPage exposing (..)

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
        column [ width fill, paddingEach { top = 100, right = 0, bottom = 0, left = 0 } ]
            [ row
                [ width fill
                , height <| px 1280
                , Background.image "%PUBLIC_URL%/assets/images/about-bg.png"
                , inFront
                    (column
                        [ centerX
                        , moveDown 230
                        , moveRight 120
                        , spacingXY 0 40
                        , width <| px 694
                        , Font.color <| rgb255 255 255 255
                        ]
                        [ row [ centerX, Font.size 28, Font.bold ] [ text (t translations "aboutPage.title") ]
                        , row [ Font.alignLeft, paddingXY 24 32, alpha 0.7, Background.color <| rgb255 45 45 45, Font.size 18 ]
                            [ textColumn [ spacingXY 0 30 ]
                                [ paragraph [ spacingXY 0 10 ] [ text (t translations "aboutPage.intro.1") ]
                                , paragraph [ spacingXY 0 10 ] [ text (t translations "aboutPage.intro.2") ]
                                , paragraph [ spacingXY 0 10 ] [ text (t translations "aboutPage.intro.3") ]
                                ]
                            ]
                        ]
                    )
                ]
                []
            , row [ width fill, paddingXY 86 0, spacingXY 70 0, Background.color <| rgb255 250 249 248 ]
                [ column []
                    [ image [ width <| px 482, height <| px 825 ]
                        { src = "%PUBLIC_URL%/assets/images/ceo.png"
                        , description = "CEO Victor"
                        }
                    ]
                , column [ paddingXY 77 80, Background.color <| rgb255 255 255 255 ]
                    [ el [ centerX, paddingEach { top = 0, right = 0, bottom = 36, left = 0 }, Font.size 28, Font.color <| rgb255 43 65 98, Font.bold ] <| text (t translations "aboutPage.ceo.title")
                    , textColumn [ Font.alignLeft, spacingXY 0 30, Font.size 16, Font.color <| rgb255 89 89 89 ]
                        [ paragraph [ spacingXY 0 10 ] [ text (t translations "aboutPage.ceo.1") ]
                        , paragraph [ spacingXY 0 10 ] [ text (t translations "aboutPage.ceo.2") ]
                        , paragraph [ spacingXY 0 10 ] [ text (t translations "aboutPage.ceo.3") ]
                        ]
                    ]
                ]
            , row [ width fill, Background.image "%PUBLIC_URL%/assets/images/about-service-bg.png", paddingEach { top = 119, left = 0, right = 0, bottom = 85 } ]
                [ column [ width fill ]
                    [ el [ centerX, paddingEach { top = 0, right = 0, bottom = 28, left = 0 }, Font.size 28, Font.color <| rgb255 255 255 255, Font.bold ] <| text (t translations "aboutPage.service.title")
                    , column [ spacingXY 34 41, centerX, Font.color <| rgb255 255 255 255, Font.size 18 ]
                        [ row [ centerX, spacing 24 ]
                            [ viewServiceItem (t translations "aboutPage.service.1")
                            , viewServiceItem (t translations "aboutPage.service.2")
                            ]
                        , row [ centerX, spacing 24 ]
                            [ viewServiceItem (t translations "aboutPage.service.3")
                            , viewServiceItem (t translations "aboutPage.service.4")
                            ]
                        , row [ centerX, spacing 24 ]
                            [ viewServiceItem (t translations "aboutPage.service.5")
                            , viewServiceItem (t translations "aboutPage.service.6")
                            ]
                        ]
                    ]
                ]
            , row [ width fill, paddingEach { top = 64, right = 0, bottom = 102, left = 0 } ]
                [ column [ width fill ]
                    [ el
                        [ centerX
                        , Font.size 28
                        , Font.color <| rgb255 43 65 98
                        , Font.bold
                        , paddingEach { top = 0, right = 0, bottom = 32, left = 0 }
                        ]
                      <|
                        text (t translations "aboutPage.team")
                    , image
                        [ width
                            (fill
                                |> maximum 1082
                            )
                        , height <| px 432
                        , centerX
                        ]
                        { src = "%PUBLIC_URL%/assets/images/team.png"
                        , description = "Team Milestone"
                        }
                    ]
                ]
            , row [ width fill, Background.color <| rgb255 250 249 248, paddingEach { top = 78, left = 0, right = 0, bottom = 155 } ]
                [ column [ width fill ]
                    [ el
                        [ centerX
                        , paddingEach { top = 64, right = 0, bottom = 32, left = 0 }
                        , Font.size 28
                        , Font.color <| rgb255 43 65 98
                        , Font.bold
                        ]
                      <|
                        text (t translations "aboutPage.office")
                    , row [ centerX ]
                        [ image
                            [ width
                                (fill
                                    |> maximum 710
                                )
                            , height <| px 428
                            , centerX
                            ]
                            { src = "%PUBLIC_URL%/assets/images/office.png"
                            , description = "Team Milestone"
                            }
                        , image
                            [ width
                                (fill
                                    |> maximum 710
                                )
                            , height <| px 428
                            , centerX
                            ]
                            { src = "%PUBLIC_URL%/assets/images/map.png"
                            , description = "Team Milestone"
                            }
                        ]
                    ]
                ]
            ]


viewServiceItem : String -> Element msg
viewServiceItem title =
    column [ width <| px 384, height <| px 106, Background.color <| rgb255 217 74 61, paddingXY 75 0, Border.rounded 4 ]
        [ paragraph [ centerX, centerY ] [ text title ]
        ]
