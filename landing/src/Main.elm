module Main exposing (init, view)

-- import Browser exposing (Document)

import Asset
import Browser
import Browser.Navigation as Nav
import Html exposing (Html, a, article, aside, b, br, button, div, em, figure, footer, h1, h2, h3, h4, header, img, li, nav, p, section, span, text, ul)
import Html.Attributes exposing (alt, class, height, href, id, src, target, width)
import Html.Events exposing (onClick)
import Http
import Json.Decode exposing (Decoder, field, int, list, map2, map3, map4, map5, map6, map7, map8, string)
import String exposing (append)
import Time
import Url
import Url.Parser exposing (Parser, map, oneOf, parse, s, top)



-- MODEL


type alias Model =
    { navBarClassNames : List String
    , serviceContentList : List ServiceContent
    , jpServiceContentList : List ServiceContent
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
    , faqList : List Faq
    , errorMsg : Maybe Http.Error
    , topIndex : Int
    , url : Url.Url
    , key : Nav.Key
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
    , date : String
    , title : String
    , link : String
    }


type alias FundRaiseStats =
    { successCaseNum : Int
    , successRate : Int
    , totalFund : String
    , funders : Int
    }


type alias Story =
    { link : String, imgSrc : String, title : String, description : String, subtitle : String, testimony : String, fundRaiseAmount : String, funders : Int }


type CarouselUseCase
    = Service
    | SuccessCase


type alias Faq =
    { question : String
    , answer : String
    }


serviceCarouselLength =
    2


assetPath =
    "%PUBLIC_URL%/assets/images/"


linkPath =
    "post/"


type CarouselBehaviour
    = Next
    | Prev


type Msg
    = TOGGLE
    | GotServiceContentList (Result Http.Error (List ServiceContent))
    | GotJpServiceContentList (Result Http.Error (List ServiceContent))
    | GotServiceDetailList (Result Http.Error (List ServiceDetail))
    | Carousel CarouselUseCase CarouselBehaviour
    | GotMediaList (Result Http.Error (List String))
    | GotPartnerList (Result Http.Error (List String))
    | GotTeamMemberList (Result Http.Error (List TeamMember))
    | GotArticleList (Result Http.Error (List Article))
    | GotStoryList (Result Http.Error (List Story))
    | GotFaqList (Result Http.Error (List Faq))
    | GotFundRaiseStats (Result Http.Error FundRaiseStats)
    | SelectTeamMember Int
    | DotClick Int
    | SwitchTopImage Time.Posix
    | LinkClicked Browser.UrlRequest
    | UrlChanged Url.Url


init : () -> Url.Url -> Nav.Key -> ( Model, Cmd Msg )
init flags url key =
    ( { navBarClassNames = []
      , serviceContentList = []
      , jpServiceContentList = []
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
      , faqList = []
      , errorMsg = Nothing
      , topIndex = 1
      , key = key
      , url = url
      }
    , Cmd.batch
        [ Http.get
            { url = "%PUBLIC_URL%/assets/data/service_content.json"
            , expect = Http.expectJson GotServiceContentList decodeServiceContentList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/jp_service_content.json"
            , expect = Http.expectJson GotJpServiceContentList decodeServiceContentList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/service_detail.json"
            , expect = Http.expectJson GotServiceDetailList decodeServiceDetailList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/media.json"
            , expect = Http.expectJson GotMediaList decodeMediaList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/partner.json"
            , expect = Http.expectJson GotPartnerList decodeMediaList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/team.json"
            , expect = Http.expectJson GotTeamMemberList decodeTeamMemberList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/article.json"
            , expect = Http.expectJson GotArticleList decodeArticleList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/story.json"
            , expect = Http.expectJson GotStoryList decodeStoryList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/faq.json"
            , expect = Http.expectJson GotFaqList decodeFaqList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/fund_raise_stats.json"
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


articleDecoder : Decoder Article
articleDecoder =
    map4 Article
        (field "imgSrc" string)
        (field "date" string)
        (field "title" string)
        (field "link" string)


decodeArticleList : Decoder (List Article)
decodeArticleList =
    field "data" (list articleDecoder)


storyDecoder : Decoder Story
storyDecoder =
    map8 Story
        (field "link" string)
        (field "imgSrc" string)
        (field "title" string)
        (field "description" string)
        (field "subtitle" string)
        (field "testimony" string)
        (field "fundRaiseAmount" string)
        (field "funders" int)


decodeStoryList : Decoder (List Story)
decodeStoryList =
    field "data" (list storyDecoder)


faqDecoder : Decoder Faq
faqDecoder =
    map2 Faq
        (field "question" string)
        (field "answer" string)


decodeFaqList : Decoder (List Faq)
decodeFaqList =
    field "data" (list faqDecoder)


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
        LinkClicked urlRequest ->
            case urlRequest of
                Browser.Internal url ->
                    ( model, Nav.pushUrl model.key (Url.toString url) )

                Browser.External href ->
                    ( model, Nav.load href )

        UrlChanged url ->
            ( { model | url = url }, Cmd.none )

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

        GotJpServiceContentList result ->
            case result of
                Ok jpServiceContentList ->
                    ( { model | jpServiceContentList = jpServiceContentList }, Cmd.none )

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

        GotFaqList result ->
            case result of
                Ok faqList ->
                    ( { model | faqList = faqList }, Cmd.none )

                Err _ ->
                    ( model, Cmd.none )

        GotFundRaiseStats result ->
            case result of
                Ok fundRaiseStats ->
                    ( { model | fundRaiseStats = fundRaiseStats }, Cmd.none )

                Err err ->
                    ( { model | errorMsg = Just err }, Cmd.none )

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
        [ nav [ class (String.join " " model.navBarClassNames) ]
            [ a [ id "logo-link", href "#top" ]
                [ figure []
                    [ img
                        [ Asset.src Asset.logo
                        , alt "logo"
                        , class "logo"
                        ]
                        []
                    ]
                ]
            , div [ class "nav-link-wrapper" ]
                [ div [ class "lang-toggle" ] [ a [ class "selected", href "/" ] [ text "TW" ], a [ href "/jp" ] [ text "JP" ] ]
                , div [ class "nav-link" ]
                    [ a [ class "consult-btn", href "https://japaninsider.typeform.com/to/yvsVAD", target "_blank" ] [ text "免費諮詢" ]
                    , a [ href "#service" ] [ text "服務內容" ]
                    , a [ href "#faq" ] [ text "常見問題" ]
                    , a [ href "#article" ] [ text "精選文章" ]
                    , a [ href "https://www.facebook.com/japaninsiders/", class "fb-logo" ]
                        [ figure []
                            [ img
                                [ Asset.src Asset.fb
                                , alt "fb logo"
                                ]
                                []
                            ]
                        ]
                    ]
                ]
            ]
        , a [ class "hamburger", onClick TOGGLE ]
            [ img [ Asset.src Asset.hamburger, width 25, height 25, alt "Menu" ] [] ]
        ]


viewJpHeader : Model -> Html Msg
viewJpHeader model =
    header []
        [ nav [ class (String.join " " model.navBarClassNames) ]
            [ a [ id "logo-link", href "#top" ]
                [ figure []
                    [ img
                        [ Asset.src Asset.logo
                        , alt "logo"
                        , class "logo"
                        ]
                        []
                    ]
                ]
            , div [ class "nav-link-wrapper" ]
                [ div [ class "lang-toggle" ] [ a [ href "/" ] [ text "TW" ], a [ class "selected", href "/jp" ] [ text "JP" ] ]
                , div [ class "nav-link" ]
                    [ a [ class "consult-btn", href "https://japaninsider.typeform.com/to/yvsVAD", target "_blank" ] [ text "お問い合わせ" ]
                    , a [ href "#service" ] [ text "事業內容" ]
                    , a [ href "#company-spirit" ] [ text "会社精神" ]
                    , a [ href "#company-summary" ] [ text "会社概要" ]
                    ]
                ]
            ]
        , a [ class "hamburger", onClick TOGGLE ]
            [ img [ Asset.src Asset.hamburger, width 25, height 25, alt "Menu" ] [] ]
        ]


viewMailBtn : Html Msg
viewMailBtn =
    div [ class "mailBtn" ]
        [ a [ href "https://japaninsider.typeform.com/to/yvsVAD" ]
            [ figure []
                [ img [ Asset.src Asset.mail, alt "mail button" ] []
                ]
            ]
        ]


viewSectionTop : Model -> Html Msg
viewSectionTop { topIndex } =
    section [ id "top", class "top" ]
        [ div [ class "hero-description" ]
            [ h2 [] [ text "Japan Insider 是提供日本群眾募資、線上電商營運、線下通路開發顧問的專業團隊" ]
            , h1 [ class "top-title" ] [ text "以群眾募資為起跑點，", br [] [], text "一起開始日本市場的開拓之旅！" ]
            , h1 [ class "top-mobile-title" ] [ text "以群眾募資為起跑點，一起開始日本市場的開拓之旅！" ]
            , p []
                [ span [] [ text "已協助" ]
                , em [] [ text "8" ]
                , span [] [ text "個團隊在日本募資" ]
                , em [] [ text "JPY 53,000,000" ]
                ]
            , div [ class "top-section-action-container" ]
                [ a
                    [ class "consult-btn", href "https://japaninsider.typeform.com/to/yvsVAD", target "_blank" ]
                    [ text "免費諮詢" ]
                , a [ class "know-more-btn", href "#service" ] [ text "了解更多" ]
                ]
            ]
        , figure []
            [ img [ class "hero-img", Asset.src Asset.flightMap, alt "hero image" ] [] ]
        ]


viewJpTop : Html Msg
viewJpTop =
    section [ id "top", class "jp-top" ]
        [ div [ class "jp-top-description" ]
            [ h2 [] [ text "Bridge Cross-Border Connection" ]
            , h1 [] [ text "台湾企業の日本進出する新モデルを創出" ]
            ]
        ]


viewSectionIntroduction : Model -> Html Msg
viewSectionIntroduction { successStoryList } =
    div [ class "introduction-background-wrapper" ]
        [ section [ id "introduction", class "introduction" ]
            [ h2 []
                [ text "日本群眾募資市場"
                ]
            , div [ class "crd-introduction" ]
                [ div [ class "crd-introduction-description" ]
                    [ p [] [ text "群眾募資在日本越來越普及，過去幾年的募資金額都有大幅成長，也漸漸成為海外新創進日本市場的前哨站。" ]
                    , p [] [ text "日本群眾募資的特色之一是平台眾多，每個平台有各自的特性及優點。每個團隊目標皆不同，必須要有相應策略指南，才能在市場的開拓旅程中勝出！" ]
                    ]
                , figure []
                    [ img [ Asset.src Asset.crowdSourcePartner, alt "crowd sourcing partner" ] []
                    ]
                ]
            , h2 [] [ text "那些年與我們一起開拓的團隊" ]
            , div [ class "success-crd" ]
                -- TODO @paipo: make carousel and take more items
                (List.map viewStory (List.take 3 successStoryList))
            ]
        ]


viewSectionService : Model -> Html Msg
viewSectionService { serviceContentList } =
    section [ id "service", class "service" ]
        [ h2 [ class "section-title" ] [ text "服務內容" ]
        , div [ class "service-content-container" ] (List.map viewServiceContent serviceContentList)
        ]


viewJpSectionService { jpServiceContentList } =
    section [ id "service", class "service" ]
        [ h2 [ class "section-title" ] [ text "事業內容" ]
        , div [ class "service-content-container" ] (List.map viewServiceContent jpServiceContentList)
        ]


viewServiceContent : ServiceContent -> Html Msg
viewServiceContent { imgSrc, imgAlt, title, description } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "service-content-item" ]
        [ h2 [] [ text title ]
        , figure [] [ img [ src imgSrcPath, alt imgAlt ] [] ]
        , p [] [ text description ]
        ]



-- viewSectionService : Model -> Html Msg
-- viewSectionService { serviceContentList, serviceDetailList, serviceIndex } =
--     section [ id "service" ]
--         [ h3 [ class "section-title" ] [ text "服務內容" ]
--         , div [ class "carousel" ]
--             [ div [ class "prev" ] [ div [ class "arrow-left", onClick (Carousel Service Prev) ] [] ]
--             , ul [ class "slider" ]
--                 [ li
--                     [ class
--                         (if serviceIndex == 0 then
--                             "visible"
--                          else
--                             ""
--                         )
--                     ]
--                     [ div [ class "three-grid-view-container" ]
--                         (List.map viewServiceContent serviceContentList)
--                     ]
--                 , li
--                     [ class
--                         (if serviceIndex == 1 then
--                             "visible"
--                          else
--                             ""
--                         )
--                     ]
--                     [ div [ class "four-grid-view-container" ]
--                         (List.map viewServiceDetail serviceDetailList)
--                     ]
--                 ]
--             , div [ class "next" ] [ div [ class "arrow-right", onClick (Carousel Service Next) ] [] ]
--             ]
--         , div [ class "mobile-list-container" ]
--             (List.map viewMobileServiceContent serviceContentList)
--         ]


viewSectionFaq : Model -> Html Msg
viewSectionFaq { faqList } =
    section [ id "faq", class "faq" ]
        [ h2 [ class "section-title" ] [ text "常見問題" ]
        , div [ class "faq-container" ] (List.map viewFaq faqList)
        ]


viewFaq { question, answer } =
    article []
        [ p [ class "faq-question" ]
            [ text ("Q: " ++ question) ]
        , p
            [ class "faq-answer" ]
            [ text ("A: " ++ answer) ]
        ]


viewSectionArticle : Model -> Html Msg
viewSectionArticle { articleList } =
    section [ id "article", class "article" ]
        [ h2 [ class "section-title" ] [ text "精選文章" ]
        , div [ class "article-container" ] (List.map viewArticle articleList)
        , a [ class "know-more-btn", href "https://www.japaninsider.co/post/post-list/" ] [ text "瀏覽更多" ]
        ]


viewArticle : Article -> Html Msg
viewArticle { imgSrc, date, title, link } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    a [ href link, class "link-container" ]
        [ article [ class "article-item" ]
            [ figure [] [ img [ src imgSrcPath, alt title ] [] ]

            -- , p [ class "article-item-date" ] [ text date ]
            , p [ class "article-item-title" ] [ text title ]
            ]
        ]


viewSectionEnterpriseRegister : Html Msg
viewSectionEnterpriseRegister =
    section [ id "enterprise-register", class "enterprise-register" ]
        [ div [ class "enterprise-register-description" ]
            [ h2 [] [ text "加入 Japan Insider 新創團隊社群" ]
            , p [] [ text "想認識更多在日本的人脈嗎?  " ]
            , p [] [ text "想在未來建立自己在日本的團隊嗎?" ]
            , p [] [ text "想了解更多日本的商業環境嗎?" ]
            , p [ class "last-line" ] [ text "立即登錄您的企業資訊，讓更多在日本的跨境人才看到您們的團隊！" ]
            , a [ class "consult-btn", href "https://www.surveycake.com/s/Xvn8m", target "_blank" ] [ text "登錄社群" ]
            ]
        , figure []
            [ img [ Asset.src Asset.enterpriseRegisterImage, alt "register as enterprise" ] [] ]
        ]


viewJpSectionEnterpriseRegister : Html Msg
viewJpSectionEnterpriseRegister =
    section [ id "enterprise-register", class "enterprise-register" ]
        [ div [ class "enterprise-register-description" ]
            [ h2 [] [ text "商品・ブランドのお問い合わせはこちら" ]
            , p [ id "jp-enterprise-register-paragraph" ]
                [ text "商品の取り扱い、輸入、物販、などに関するご質問は以下のフォームにご記入いただけますと幸いです。" ]
            , a [ class "consult-btn", href "https://www.surveycake.com/s/Xvn8m", target "_blank" ] [ text "お問い合わせ" ]
            ]
        , figure []
            [ img [ Asset.src Asset.jpEnterpriseRegisterImage, alt "consult as enterprise" ] [] ]
        ]


viewSectionTeam : Html Msg
viewSectionTeam =
    div [ id "team", class "team" ]
        [ h2 [ class "section-title" ] [ text "團隊成員" ]
        , div [ class "team-description" ]
            [ h2 []
                [ text "JAPAN INSIDER 成員皆位於日本，精通中、日、英文，背景包括數位行銷、產品設計、軟體開發、工程、供應鏈等。讓熟悉日本市場的專業團隊，成為您專案的一份子，協助您進入日本市場。"
                ]
            ]
        ]


viewMobileServiceContent : ServiceContent -> Html Msg
viewMobileServiceContent { imgSrc, imgAlt, title, description } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "list-item no-bottom-border" ]
        [ div [ class "circle-container" ] [ figure [] [ img [ src imgSrcPath, alt imgAlt ] [] ] ]
        , h3 [ class "custom-list-item-title" ] [ text title ]
        , p [] [ text description ]
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


viewStory : Story -> Html Msg
viewStory { link, imgSrc, title, description, testimony, fundRaiseAmount, funders, subtitle } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "story-item" ]
        [ h2 [ class "fund-raise-title" ] [ text (title ++ " 成功募資 " ++ fundRaiseAmount ++ " 萬日幣") ]
        , div [ class "fund-raise-content" ]
            [ p [ class "fund-raise-description" ] [ text description ]
            , p [ class "fund-raise-testimony" ] [ text testimony ]
            ]
        , img [ class "fund-raise-image", src imgSrcPath, alt title ] []
        , p [ class "fund-raise-subtitle" ] [ text subtitle ]
        , a [ class "know-more-btn", href link, target "_blank" ] [ text "募資頁面" ]
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


viewJpSectionSpirit : Html Msg
viewJpSectionSpirit =
    section [ id "company-spirit", class "jp-spirit" ]
        [ h2 [] [ text "会社精神" ]
        , div [ class "jp-spirit-content-wrapper" ]
            [ h2 [] [ text "「日本にいる外人たちの力を発揮」そして「外から日本にイノベーションをもたらすこと」" ]
            , p [] [ text "リソースが少ないうち、ビジネスの成長が急務、海外（日本）展開を急がなければというベンチャー企業に対して、日本語が話せる、日本の商習慣が理解できる、かつ日本におり、現地での対応が可能な「Japan Insider」(在日外国人)の力を借りて日本進出をサポートするというモデルが私だちのソリューションです。" ]
            , p [] [ text "次世代の進出モデルを構築することで日本の市場を活発させ、イノベーションをもたらすことができると私たちは信じています。現在まで数多くの海外ベンチャーの商品を日本市場に浸透させ、市場テスト、ネットワークの連携や売り上げアップなどに貢献してきました。" ]
            ]
        ]


viewJpSectionSummary : Html Msg
viewJpSectionSummary =
    section [ id "company-summary", class "jp-summary" ]
        [ h2 [] [ text "会社概要" ]
        , div [ class "jp-summary-content-wrapper" ]
            [ p [] [ b [] [ text "住所" ], span [] [ text "〒106-00046 東京都港区元麻布3-1-6 Blink Smart WorkSpace" ] ]
            , p [] [ b [] [ text "E-mail" ], span [] [ text "contact@japaninsider.co" ] ]
            , p [] [ b [] [ text "成立" ], span [] [ text "2018年12月" ] ]
            , p [] [ b [] [ text "資本金" ], span [] [ text "3,000,000円" ] ]
            , p [] [ b [] [ text "取引銀行" ], span [] [ text "三菱UFJ銀行" ] ]
            , p [] [ b [] [ text "事業内容" ], span [] [ text "輸入・事業・WEB販促コンサルティング" ] ]
            ]
        ]


viewFooter : Html Msg
viewFooter =
    footer []
        [ div [ class "footer-info" ]
            [ figure []
                [ img [ Asset.src Asset.whiteLogo, alt "logo", class "footer-logo" ] [] ]
            , p
                [ class "about-us-email" ]
                [ text "contact@japaninsider.co" ]
            , p
                [ class "about-us-address" ]
                [ text "106-0046 東京都港区元麻布3-1-6" ]
            ]
        ]


viewJpFooter : Html Msg
viewJpFooter =
    footer []
        [ div [ class "footer-info" ]
            [ figure []
                [ img [ Asset.src Asset.whiteLogo, alt "logo", class "footer-logo" ] [] ]
            , p
                [ class "about-us-email" ]
                [ text "contact@japaninsider.co" ]
            , p
                [ class "about-us-address" ]
                [ text "106-0046 東京都港区元麻布3-1-6" ]
            ]
        ]


type Route
    = Home
    | JpHome
    | NotFound


route : Parser (Route -> a) a
route =
    oneOf
        [ map Home top
        , map JpHome (s "jp")
        ]


toRoute : String -> Route
toRoute string =
    case Url.fromString string of
        Nothing ->
            NotFound

        Just url ->
            Maybe.withDefault NotFound (parse route url)


view : Model -> Browser.Document Msg
view model =
    { title = "日本インサイド"
    , body =
        case toRoute (Url.toString model.url) of
            Home ->
                [ viewHeader model

                -- , viewMailBtn
                , viewSectionTop model
                , viewSectionIntroduction model
                , viewSectionService model
                , viewSectionFaq model
                , viewSectionArticle model
                , viewSectionEnterpriseRegister
                , viewSectionTeam
                , viewFooter
                ]

            JpHome ->
                [ viewJpHeader model
                , viewJpTop
                , viewJpSectionService model
                , viewJpSectionEnterpriseRegister
                , viewJpSectionSpirit
                , viewJpSectionSummary
                , viewJpFooter
                ]

            _ ->
                [ text "Something WRong" ]
    }



-- PROGRAM


main : Program () Model Msg
main =
    Browser.application
        { init = init
        , view = view
        , subscriptions = subscriptions
        , update = update
        , onUrlChange = UrlChanged
        , onUrlRequest = LinkClicked
        }