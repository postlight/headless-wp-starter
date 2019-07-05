module Main exposing (init, view)

import Browser exposing (Document)
import Browser.Navigation
import Html exposing (Html, a, article, aside, div, em, figure, footer, h2, h3, h4, header, img, li, nav, p, section, span, text, ul)
import Html.Attributes exposing (..)
import Html.Events exposing (onClick)
import Http
import Json.Decode exposing (Decoder, field, int, list, map2, map3, map4, map5, map6, string)
import String exposing (append)
import Time



-- MODEL


type alias Model =
    { navBarClassNames : List String
    , serviceContentList : List ServiceContent
    , serviceDetailList : List ServiceDetail
    , serviceIndex : Int
    , successCaseIndex : Int
    , mediaList : List String
    , partnerList : List String
    , teamMemberList : List TeamMember
    , selectedTeamMemberIndex : Int
    , articleList : List Article
    , fundRaiseStats : FundRaiseStats
    , successStoryList : List Story
    , errorMsg : Maybe Http.Error
    , topIndex : Int
    }


type alias ServiceContent =
    { imgSrc : String
    , imgAlt : String
    , title : String
    , description : String
    }


type alias ServiceDetail =
    { title : String
    , description : String
    }


type alias TeamMember =
    { name : String
    , imgSrc : String
    , position : String
    , introduction : String
    }


type alias Date =
    { year : String
    , month : String
    , day : String
    }


type alias Article =
    { imgSrc : String
    , date : Date
    , title : String
    , description : String
    , link : String
    }


type alias FundRaiseStats =
    { successCaseNum : Int
    , successRate : Int
    , totalFund : String
    , funders : Int
    }


type alias Story =
    { link : String, imgSrc : String, title : String, description : String, fundRaiseAmount : String, funders : Int }


type CarouselUseCase
    = Service
    | SuccessCase


serviceCarouselLength =
    2


assetPath =
    "img/"


linkPath =
    "post/"


type CarouselBehaviour
    = Next
    | Prev


type Msg
    = TOGGLE
    | GotServiceContentList (Result Http.Error (List ServiceContent))
    | GotServiceDetailList (Result Http.Error (List ServiceDetail))
    | Carousel CarouselUseCase CarouselBehaviour
    | GotMediaList (Result Http.Error (List String))
    | GotPartnerList (Result Http.Error (List String))
    | GotTeamMemberList (Result Http.Error (List TeamMember))
    | GotArticleList (Result Http.Error (List Article))
    | GotStoryList (Result Http.Error (List Story))
    | GotFundRaiseStats (Result Http.Error FundRaiseStats)
    | LinkToUrl String
    | SelectTeamMember Int
    | DotClick Int
    | SwitchTopImage Time.Posix


init : () -> ( Model, Cmd Msg )
init _ =
    ( { navBarClassNames = []
      , serviceContentList = []
      , serviceDetailList = []
      , serviceIndex = 0
      , successCaseIndex = 0
      , mediaList = []
      , partnerList = []
      , teamMemberList = []
      , selectedTeamMemberIndex = -1
      , articleList = []
      , fundRaiseStats = { successCaseNum = 0, successRate = 0, totalFund = "", funders = 0 }
      , successStoryList = []
      , errorMsg = Nothing
      , topIndex = 1
      }
    , Cmd.batch
        [ Http.get
            { url = "../data/service_content.json"
            , expect = Http.expectJson GotServiceContentList decodeServiceContentList
            }
        , Http.get
            { url = "../data/service_detail.json"
            , expect = Http.expectJson GotServiceDetailList decodeServiceDetailList
            }
        , Http.get
            { url = "../data/media.json"
            , expect = Http.expectJson GotMediaList decodeMediaList
            }
        , Http.get
            { url = "../data/partner.json"
            , expect = Http.expectJson GotPartnerList decodeMediaList
            }
        , Http.get
            { url = "../data/team.json"
            , expect = Http.expectJson GotTeamMemberList decodeTeamMemberList
            }
        , Http.get
            { url = "../data/article.json"
            , expect = Http.expectJson GotArticleList decodeArticleList
            }
        , Http.get
            { url = "../data/story.json"
            , expect = Http.expectJson GotStoryList decodeStoryList
            }
        , Http.get
            { url = "../data/fund_raise_stats.json"
            , expect = Http.expectJson GotFundRaiseStats decodeFundRaiseStats
            }
        ]
    )



-- JSON DECODE


decodeServiceContentList : Decoder (List ServiceContent)
decodeServiceContentList =
    field "data" (list serviceContentDecoder)


serviceContentDecoder : Decoder ServiceContent
serviceContentDecoder =
    map4 ServiceContent
        (field "imgSrc" string)
        (field "imgAlt" string)
        (field "title" string)
        (field "description" string)


decodeServiceDetailList : Decoder (List ServiceDetail)
decodeServiceDetailList =
    field "data" (list serviceDetailDecoder)


serviceDetailDecoder : Decoder ServiceDetail
serviceDetailDecoder =
    map2 ServiceDetail
        (field "title" string)
        (field "description" string)


decodeMediaList : Decoder (List String)
decodeMediaList =
    field "data" (list string)


teamMemberDecoder : Decoder TeamMember
teamMemberDecoder =
    map4 TeamMember
        (field "name" string)
        (field "imgSrc" string)
        (field "position" string)
        (field "introduction" string)


decodeTeamMemberList : Decoder (List TeamMember)
decodeTeamMemberList =
    field "data" (list teamMemberDecoder)


dateDecoder : Decoder Date
dateDecoder =
    map3 Date
        (field "year" string)
        (field "month" string)
        (field "day" string)


articleDecoder : Decoder Article
articleDecoder =
    map5 Article
        (field "imgSrc" string)
        (field "date" dateDecoder)
        (field "title" string)
        (field "description" string)
        (field "link" string)


decodeArticleList : Decoder (List Article)
decodeArticleList =
    field "data" (list articleDecoder)


storyDecoder : Decoder Story
storyDecoder =
    map6 Story
        (field "link" string)
        (field "imgSrc" string)
        (field "title" string)
        (field "description" string)
        (field "fundRaiseAmount" string)
        (field "funders" int)


decodeStoryList : Decoder (List Story)
decodeStoryList =
    field "data" (list storyDecoder)


decodeFundRaiseStats : Decoder FundRaiseStats
decodeFundRaiseStats =
    map4 FundRaiseStats
        (field "successCaseNum" int)
        (field "successRate" int)
        (field "totalFund" string)
        (field "funders" int)



-- UPDATE


update : Msg -> Model -> ( Model, Cmd Msg )
update msg model =
    let
        successCaseCarouselLength =
            round (toFloat (List.length model.successStoryList) / 3) + 1
    in
    case msg of
        TOGGLE ->
            case List.length model.navBarClassNames of
                0 ->
                    ( { model | navBarClassNames = "responsive" :: model.navBarClassNames }
                    , Cmd.none
                    )

                _ ->
                    ( { model | navBarClassNames = [] }, Cmd.none )

        GotServiceContentList result ->
            case result of
                Ok serviceContentList ->
                    ( { model | serviceContentList = serviceContentList }, Cmd.none )

                Err _ ->
                    ( model, Cmd.none )

        GotServiceDetailList result ->
            case result of
                Ok serviceDetailList ->
                    ( { model | serviceDetailList = serviceDetailList }, Cmd.none )

                Err _ ->
                    ( model, Cmd.none )

        Carousel useCase behaviour ->
            case useCase of
                Service ->
                    case behaviour of
                        Next ->
                            ( { model | serviceIndex = nextIndex model.serviceIndex serviceCarouselLength }, Cmd.none )

                        Prev ->
                            ( { model | serviceIndex = prevIndex model.serviceIndex serviceCarouselLength }, Cmd.none )

                SuccessCase ->
                    case behaviour of
                        Next ->
                            ( { model | successCaseIndex = nextIndex model.successCaseIndex successCaseCarouselLength }, Cmd.none )

                        Prev ->
                            ( { model | successCaseIndex = prevIndex model.successCaseIndex successCaseCarouselLength }, Cmd.none )

        GotMediaList result ->
            case result of
                Ok mediaList ->
                    ( { model | mediaList = mediaList }, Cmd.none )

                Err _ ->
                    ( model, Cmd.none )

        GotPartnerList result ->
            case result of
                Ok partnerList ->
                    ( { model | partnerList = partnerList }, Cmd.none )

                Err _ ->
                    ( model, Cmd.none )

        GotTeamMemberList result ->
            case result of
                Ok teamMemberList ->
                    ( { model | teamMemberList = teamMemberList }, Cmd.none )

                Err _ ->
                    ( model, Cmd.none )

        GotArticleList result ->
            case result of
                Ok articleList ->
                    ( { model | articleList = articleList }, Cmd.none )

                Err _ ->
                    ( model, Cmd.none )

        GotStoryList result ->
            case result of
                Ok successStoryList ->
                    ( { model | successStoryList = successStoryList }, Cmd.none )

                Err _ ->
                    ( model, Cmd.none )

        GotFundRaiseStats result ->
            case result of
                Ok fundRaiseStats ->
                    ( { model | fundRaiseStats = fundRaiseStats }, Cmd.none )

                Err err ->
                    ( { model | errorMsg = Just err }, Cmd.none )

        LinkToUrl link ->
            ( model, Browser.Navigation.load link )

        SelectTeamMember index ->
            ( { model | selectedTeamMemberIndex = index }, Cmd.none )

        DotClick index ->
            ( { model | topIndex = index }, Cmd.none )

        SwitchTopImage _ ->
            case model.topIndex of
                3 ->
                    ( { model | topIndex = 1 }, Cmd.none )

                _ ->
                    ( { model | topIndex = model.topIndex + 1 }, Cmd.none )


nextIndex : Int -> Int -> Int
nextIndex currentIndex maxIndex =
    if currentIndex + 1 == maxIndex then
        0

    else
        currentIndex + 1


prevIndex : Int -> Int -> Int
prevIndex currentIndex maxIndex =
    if currentIndex - 1 < 0 then
        maxIndex - 1

    else
        currentIndex - 1



-- SUBSCRIPTIONS


subscriptions : Model -> Sub Msg
subscriptions model =
    Time.every 3000 SwitchTopImage



-- VIEW


viewHeader : Model -> Html Msg
viewHeader model =
    header []
        [ a [ id "logo-link", href "#top" ]
            [ figure []
                [ img
                    [ src "img/logo.svg"
                    , alt "logo"
                    , width 25
                    , height 25
                    , alt "menu"
                    ]
                    []
                ]
            ]
        , nav [ class (String.join " " model.navBarClassNames) ]
            [ a [ id "top-link", href "#top", class "selected" ] [ text "首頁" ]
            , a [ href "#service" ] [ text "服務內容" ]
            , a [ href "#success-case" ] [ text "過去實績" ]
            , a [ href "#team" ] [ text "團隊成員" ]
            , a [ href "#japan-insider" ] [ text "日本內幕" ]
            , a [ href "https://japaninsider.typeform.com/to/S7rcLo" ] [ text "聯絡我們" ]
            ]
        , a [ class "hamburger", onClick TOGGLE ]
            [ img [ src "./img/hamburger.svg", width 25, height 25 ] [] ]
        ]


viewMailBtn : Html Msg
viewMailBtn =
    div [ class "mailBtn" ]
        [ a [ href "https://japaninsider.typeform.com/to/S7rcLo" ]
            [ figure []
                [ img [ src "./img/mail.svg", alt "mail button" ] []
                ]
            ]
        ]


viewSectionTop : Model -> Html Msg
viewSectionTop { topIndex } =
    section [ id "top", style "background-image" ("url('./img/top-" ++ String.fromInt topIndex ++ ".jpg')") ]
        [ aside []
            [ h2 [] [ text "成為你日本現地的即時成員" ]
            , h2 [] [ text "支援日本群眾募資的專業團隊" ]
            ]
        , div [ class "dot-container" ]
            [ div
                [ id "dot-1"
                , class
                    ("dot"
                        ++ (if topIndex == 1 then
                                " selected"

                            else
                                ""
                           )
                    )
                , onClick (DotClick 1)
                ]
                []
            , div
                [ id "dot-2"
                , class
                    ("dot"
                        ++ (if topIndex == 2 then
                                " selected"

                            else
                                ""
                           )
                    )
                , onClick (DotClick 2)
                ]
                []
            , div
                [ id "dot-3"
                , class
                    ("dot"
                        ++ (if topIndex == 3 then
                                " selected"

                            else
                                ""
                           )
                    )
                , onClick (DotClick 3)
                ]
                []
            ]
        ]


viewSectionIntroduction : Html Msg
viewSectionIntroduction =
    section [ id "id", class "intro-description" ]
        [ h2 []
            [ text "提供日本群眾募資"
            , em [] [ text "在地化、全方位" ]
            , text "支援"
            ]
        , h2 [] [ text "成功經驗累積成的專業流程" ]
        , h2 []
            [ text "能依據團隊需求為您"
            , em [] [ text "客製化" ]
            , text "服務"
            ]
        ]


viewSectionService : Model -> Html Msg
viewSectionService { serviceContentList, serviceDetailList, serviceIndex } =
    section [ id "service" ]
        [ h3 [ class "section-title" ] [ text "服務內容" ]
        , div [ class "carousel" ]
            [ div [ class "prev" ] [ div [ class "arrow-left", onClick (Carousel Service Prev) ] [] ]
            , ul [ class "slider" ]
                [ li
                    [ class
                        (if serviceIndex == 0 then
                            "visible"

                         else
                            ""
                        )
                    ]
                    [ div [ class "three-grid-view-container" ]
                        (List.map viewServiceContent serviceContentList)
                    ]
                , li
                    [ class
                        (if serviceIndex == 1 then
                            "visible"

                         else
                            ""
                        )
                    ]
                    [ div [ class "four-grid-view-container" ]
                        (List.map viewServiceDetail serviceDetailList)
                    ]
                ]
            , div [ class "next" ] [ div [ class "arrow-right", onClick (Carousel Service Next) ] [] ]
            ]
        , div [ class "mobile-list-container" ]
            (List.map viewMobileServiceContent serviceContentList)
        ]


viewServiceContent : ServiceContent -> Html Msg
viewServiceContent { imgSrc, imgAlt, title, description } =
    article [ class "three-grid-item" ]
        [ div [ class "circle-container" ] [ figure [] [ img [ src imgSrc, alt imgAlt ] [] ] ]
        , h3 [ class "custom-list-item-title" ] [ text title ]
        , p [ class "custom-list-item-description" ] [ text description ]
        ]


viewMobileServiceContent : ServiceContent -> Html Msg
viewMobileServiceContent { imgSrc, imgAlt, title, description } =
    article [ class "list-item no-bottom-border" ]
        [ div [ class "circle-container" ] [ figure [] [ img [ src imgSrc, alt imgAlt ] [] ] ]
        , h3 [ class "custom-list-item-title" ] [ text title ]
        , p [ class "custom-list-item-description" ] [ text description ]
        ]


viewServiceDetail : ServiceDetail -> Html Msg
viewServiceDetail { title, description } =
    article [ class "circle-container list-item circle-item-content no-bottom-border big-circle yellow-border" ]
        [ h3 [ class "circle-item-title" ] [ text title ]
        , p [ class "circle-item-description" ] [ text description ]
        ]


viewSectionPromotion : Html Msg
viewSectionPromotion =
    section [ id "promotion", class "intro-description" ]
        [ h2 [ class "text" ] [ text "JAPAN INSIDER" ]
        , h2 [ class "text" ] [ text "協助團隊成功募資的金額" ]
        , h2 [] [ em [] [ text "超過 4000萬日幣" ] ]
        ]


viewSectionSuccessCase : Model -> Html Msg
viewSectionSuccessCase { fundRaiseStats, successStoryList, successCaseIndex } =
    section [ id "success-case" ]
        [ h3 [ class "section-title" ] [ text "過去實績" ]
        , div [ class "carousel" ]
            [ div [ class "prev" ] [ div [ class "arrow-left", onClick (Carousel SuccessCase Prev) ] [] ]
            , ul [ class "slider" ]
                ([ li
                    [ class
                        (if successCaseIndex == 0 then
                            "visible"

                         else
                            ""
                        )
                    ]
                    [ viewSuccessResult fundRaiseStats ]
                 ]
                    ++ dynamicallyInsertSuccessStoryCarouselItem
                        successStoryList
                        successCaseIndex
                        1
                )
            , div [ class "next" ] [ div [ class "arrow-right", onClick (Carousel SuccessCase Next) ] [] ]
            ]
        , viewMobileSuccessResult fundRaiseStats
        , div [ class "mobile-list-container" ] (List.map viewMobileStory successStoryList)
        ]


dynamicallyInsertSuccessStoryCarouselItem : List Story -> Int -> Int -> List (Html Msg)
dynamicallyInsertSuccessStoryCarouselItem storyList currentCarouselIndex currentStoryListIndex =
    let
        firstThreeStoryList =
            List.take 3 storyList

        restStoryList =
            List.drop 3 storyList
    in
    case List.length firstThreeStoryList of
        0 ->
            []

        _ ->
            [ li
                [ class
                    (if currentCarouselIndex == currentStoryListIndex then
                        "visible"

                     else
                        ""
                    )
                ]
                [ div [ class "three-grid-view-container" ]
                    (List.map viewStory firstThreeStoryList)
                ]
            ]
                ++ dynamicallyInsertSuccessStoryCarouselItem restStoryList currentCarouselIndex (currentStoryListIndex + 1)


viewSuccessResult : FundRaiseStats -> Html Msg
viewSuccessResult fundRaiseStats =
    div [ class "four-grid-view-container" ]
        [ article [ class "four-grid-item" ]
            [ h2 [ class "success-title" ]
                [ text "執行募資案" ]
            , p [ class "success-number success-circle-container" ] [ text (String.fromInt fundRaiseStats.successCaseNum) ]
            ]
        , article [ class "four-grid-item" ]
            [ h2 [ class "success-title" ]
                [ text "募資成功率" ]
            , p [ class "success-number success-circle-container" ] [ text (String.fromInt fundRaiseStats.successRate ++ "%") ]
            ]
        , article [ class "four-grid-item" ]
            [ h2 [ class "success-title" ]
                [ text "募資總金額" ]
            , p [ class "success-number success-circle-container red-background" ]
                [ text ("¥" ++ fundRaiseStats.totalFund)
                , span [ class "small-font-size" ] [ text "Million" ]
                ]
            ]
        , article [ class "four-grid-item" ]
            [ h2 [ class "success-title" ]
                [ text "募資支持者" ]
            , p [ class "success-number success-circle-container" ]
                [ text (String.fromInt fundRaiseStats.funders) ]
            ]
        ]


viewMobileSuccessResult : FundRaiseStats -> Html Msg
viewMobileSuccessResult fundRaiseStats =
    div [ class "mobile-flex-container" ]
        [ article [ class "list-item no-bottom-border small-list-item" ]
            [ h2 [ class "success-title" ]
                [ text "執行募資案" ]
            , p [ class "success-number success-circle-container" ] [ text (String.fromInt fundRaiseStats.successCaseNum) ]
            ]
        , article [ class "list-item no-bottom-border small-list-item" ]
            [ h2 [ class "success-title" ]
                [ text "募資成功率" ]
            , p [ class "success-number success-circle-container" ] [ text (String.fromInt fundRaiseStats.successRate ++ "%") ]
            ]
        , article [ class "list-item no-bottom-border small-list-item" ]
            [ h2 [ class "success-title" ]
                [ text "募資總金額" ]
            , p [ class "success-number success-circle-container red-background" ]
                [ text ("¥" ++ fundRaiseStats.totalFund)
                , span [ class "small-font-size" ] [ text "Million" ]
                ]
            ]
        , article [ class "list-item no-bottom-border small-list-item" ]
            [ h2 [ class "success-title" ]
                [ text "募資支持者" ]
            , p [ class "success-number success-circle-container" ]
                [ text (String.fromInt fundRaiseStats.funders) ]
            ]
        ]


viewSectionTeamIntroduction : Html Msg
viewSectionTeamIntroduction =
    section [ id "team-introduction" ]
        [ div [ class "float-title" ]
            [ h2 []
                [ text "JAPAN INSIDER 成員背景包括"
                , em [] [ text "工程、供應鍊、數位行銷、產品設計," ]
                , text "並且皆任職於其專業領域位於日本的公司。透過對日本市場熟悉的專業團隊, 成為你專案的一份子, 協助你進入日本市場。"
                ]
            ]
        ]


viewSectionTeam : Model -> Html Msg
viewSectionTeam { teamMemberList, selectedTeamMemberIndex } =
    section [ id "team" ]
        [ h3 [ class "section-title" ] [ text "團隊成員" ]
        , div [ class "three-grid-view-container" ] (List.indexedMap (viewTeamMember selectedTeamMemberIndex) teamMemberList)
        , div [ class "mobile-list-container" ] (List.map viewMobileTeamMember teamMemberList)
        ]


viewTeamMember : Int -> Int -> TeamMember -> Html Msg
viewTeamMember selectedTeamMemberIndex index { name, imgSrc, position, introduction } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article
        [ class
            ("three-grid-item black-border-bottom"
                ++ (if selectedTeamMemberIndex == index then
                        " selected"

                    else
                        ""
                   )
            )
        , onClick (SelectTeamMember index)
        ]
        [ div [ class "self-introduction" ] [ text introduction ]
        , img [ src imgSrcPath, alt imgSrc ] []
        , p [ class "list-item-title" ] [ text position ]
        , div [ class "list-item-description align-left" ]
            [ text name, div [ class "big-arrow" ] [] ]
        ]


viewMobileTeamMember : TeamMember -> Html Msg
viewMobileTeamMember { name, imgSrc, position, introduction } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "list-item" ]
        [ div [ class "self-introduction" ] [ text introduction ]
        , img [ src imgSrcPath, alt imgSrc ] []
        , p [ class "list-item-title" ] [ text position ]
        , div [ class "list-item-description" ] [ text name, div [ class "big-arrow" ] [] ]
        ]


viewSectionJapanInsider : Model -> Html Msg
viewSectionJapanInsider { articleList } =
    section [ id "japan-insider" ]
        [ h3 [ class "section-title" ] [ text "日本內幕部落格" ]
        , div [ class "three-grid-view-container" ] (List.map viewArticle articleList)
        , div [ class "mobile-list-container" ] (List.map viewMobileArticle articleList)
        ]


viewArticle : Article -> Html Msg
viewArticle { imgSrc, date, title, description, link } =
    let
        imgSrcPath =
            append assetPath imgSrc

        linkHrefPath =
            append linkPath link
    in
    article [ class "three-grid-item list-item-shadow" ]
        [ img [ class "big-image red-bottome-border", src imgSrcPath, alt title ] []
        , div [ class "blog-text-content" ]
            [ div [ class "blog-item-header" ]
                [ div [ class "blog-item-date" ]
                    [ p [] [ text (date.year ++ ".") ]
                    , p [] [ text (date.month ++ "." ++ date.day) ]
                    ]
                , div [ class "blog-item-title" ] [ text title ]
                ]
            , p [ class "blog-item-description" ] [ text description ]
            , a [ class "continue-reading", href linkHrefPath ] [ text "(...繼續閱讀)" ]
            ]
        ]


viewMobileArticle : Article -> Html Msg
viewMobileArticle { imgSrc, date, title, description, link } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "list-item list-item-shadow no-bottom-border" ]
        [ img [ class "red-bottome-border", src imgSrcPath, alt title ] []
        , div [ class "blog-text-content" ]
            [ div [ class "blog-item-header" ] []
            , div [ class "blog-item-date" ]
                [ p [] [ text (date.year ++ ".") ]
                , p [] [ text (date.month ++ "." ++ date.day) ]
                ]
            , div [ class "blog-item-title" ] [ text title ]
            , p [ class "blog-item-description" ] [ text description ]
            , a [ class "continue-reading", href link ] [ text "(...繼續閱讀)" ]
            ]
        ]


viewStory : Story -> Html Msg
viewStory { link, imgSrc, title, description, fundRaiseAmount, funders } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "three-grid-item list-item-shadow fund-raise-link", onClick (LinkToUrl link) ]
        [ img [ class "fund-raise-image", src imgSrcPath, alt title ] []
        , div [ class "fund-raise-content" ]
            [ h3 [ class "fund-raise-title" ] [ text title ]
            , p [ class "fund-raise-description" ] [ text description ]
            , div [ class "fund-raise-detail" ]
                [ h4 [] [ text "FUND RAISED:" ]
                , p [ class "fund-raise-amount" ] [ text ("¥" ++ fundRaiseAmount) ]
                , p [ class "funder-numbers" ] [ text ("funder" ++ String.fromInt funders) ]
                ]
            ]
        ]


viewMobileStory : Story -> Html Msg
viewMobileStory { link, imgSrc, title, description, fundRaiseAmount, funders } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "list-item fund-raise-link", onClick (LinkToUrl link) ]
        [ img [ class "fund-raise-image", src imgSrcPath, alt title ] []
        , div [ class "fund-raise-content" ]
            [ h3 [ class "fund-raise-title" ] [ text title ]
            , p [ class "fund-raise-description" ] [ text description ]
            , div [ class "fund-raise-detail" ]
                [ h4 [] [ text "FUND RAISED:" ]
                , p [ class "fund-raise-amount" ] [ text ("¥" ++ fundRaiseAmount) ]
                , p [ class "funder-numbers" ] [ text ("funder" ++ String.fromInt funders) ]
                ]
            ]
        ]


viewSectionMarketDev : Html Msg
viewSectionMarketDev =
    section [ id "market-development-description" ]
        [ div [ class "float-title" ]
            [ h3 [ class "section-title" ] [ text "後續市場開發" ]
            , h2 [ class "promoting-title" ]
                [ text "JAPAN INSIDER 除了協助團隊規劃群眾募資外, 並協助團隊後續的市場開發。已經成功協助團隊以各種方式開拓日本市場, 包括與"
                , em [] [ text "當地知名品牌談定合作, 導入當地通路商、進入日本電商平台販賣" ]
                , text "等。"
                , a [ id "amazon-link", href "https://japaninsider.typeform.com/to/PXWmex", target "_blank" ]
                    [ text "日本電商代營運" ]
                ]
            ]
        ]


viewSectionPartner : Model -> Html Msg
viewSectionPartner { partnerList } =
    section [ class "intro-description white-background" ]
        [ h3 [ class "section-title" ] [ text "合作夥伴" ]
        , h2 [ class "partner-title" ] [ text "Japan Insider 與日本各大群眾募資平台皆有合作關係, 根據團隊的產品屬性及目標, 協助你連結最適合的平台, 執行募資策略。" ]
        , div [ class "media-container" ]
            (List.map viewPartner partnerList)
        ]


viewPartner : String -> Html Msg
viewPartner imgName =
    let
        imgSrc =
            append assetPath imgName

        imgAlt =
            imgName
    in
    figure [] [ img [ class "media-image big-image", src imgSrc, alt imgAlt ] [] ]


viewSectionMedia : Model -> Html Msg
viewSectionMedia { mediaList } =
    section [ id "media" ]
        [ h3 [ class "section-title" ] [ text "媒體報導" ]
        , div [ class "media-container" ]
            (List.map viewMedia mediaList)
        ]


viewMedia : String -> Html Msg
viewMedia imgName =
    let
        imgSrc =
            append assetPath imgName

        imgAlt =
            imgName
    in
    figure [] [ img [ class "media-image", src imgSrc, alt imgAlt ] [] ]


viewFooter : Html Msg
viewFooter =
    footer []
        [ figure [] [ img [ class "media-image", src "img/logo.svg", alt "logo" ] [] ]
        , div [ class "about-us-footer" ]
            [ p [ class "about-us-title" ]
                [ text "關於我們" ]
            , p
                [ class "about-us-info" ]
                [ text "contact@japaninsider.co" ]
            , p
                [ class "about-us-info" ]
                [ text "106-0046 東京都港区元麻布3-1-6" ]
            ]
        ]


view : Model -> Document Msg
view model =
    { title = "日本インサイド"
    , body =
        [ viewHeader model
        , viewMailBtn
        , viewSectionTop model
        , viewSectionIntroduction
        , viewSectionService model
        , viewSectionPromotion
        , viewSectionSuccessCase model
        , viewSectionTeamIntroduction
        , viewSectionTeam model
        , viewSectionJapanInsider model
        , viewSectionMarketDev
        , viewSectionPartner model
        , viewSectionMedia model
        , viewFooter
        ]
    }


main : Program () Model Msg
main =
    Browser.document
        { init = init
        , view = view
        , subscriptions = subscriptions
        , update = update
        }
