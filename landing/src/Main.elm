module Main exposing (init, view)

-- import Browser exposing (Document)

import Asset
import Browser
import Browser.Navigation as Nav
import Element as E
import Element.Background as Background
import Element.Border as Border
import Element.Font as Font
import Html exposing (Html, a, article, aside, b, br, button, div, em, figure, footer, form, h1, h2, h3, h4, header, img, input, label, li, nav, p, section, span, text, ul)
import Html.Attributes exposing (action, alt, class, for, height, href, id, method, name, novalidate, placeholder, required, src, style, tabindex, target, type_, value, width)
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
    , serviceCategoryList : List ServiceCategory
    , jpServiceContentList : List ServiceContent
    , serviceDetailList : List ServiceDetail
    , serviceIndex : Int
    , successCaseIndex : Int
    , mediaList : List String
    , partnerList : List String
    , teamMemberList : List TeamMember
    , talentList : List Talent
    , selectedTalentCategory : Int
    , selectedTeamMemberIndex : Int
    , articleList : List Article
    , benefitList : List Benefit
    , fundRaiseStats : FundRaiseStats
    , successStoryList : List Story
    , faqList : List Faq
    , errorMsg : Maybe Http.Error
    , topIndex : Int
    , url : Url.Url
    , key : Nav.Key
    }


type alias ServiceCategory =
    { imgSrc : String
    , title : String
    , titleEng : String
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


type alias Benefit =
    { imgSrc : String, title : String, description : String }


type alias Talent =
    { id : Int, imgSrc : String, field : String, fieldEng : String, name : String, services : List String, intro : String }


serviceCarouselLength =
    2


assetPath =
    "%PUBLIC_URL%/assets/images/"


linkPath =
    "post/"


type CarouselBehaviour
    = Next
    | Prev


type TalentCategory
    = Marketing
    | Design
    | Operation


type Msg
    = TOGGLE
    | GotServiceContentList (Result Http.Error (List ServiceContent))
    | GotJpServiceContentList (Result Http.Error (List ServiceContent))
    | GotServiceCategoryList (Result Http.Error (List ServiceCategory))
    | GotServiceDetailList (Result Http.Error (List ServiceDetail))
    | Carousel CarouselUseCase CarouselBehaviour
    | GotMediaList (Result Http.Error (List String))
    | GotPartnerList (Result Http.Error (List String))
    | GotTeamMemberList (Result Http.Error (List TeamMember))
    | GotArticleList (Result Http.Error (List Article))
    | GotStoryList (Result Http.Error (List Story))
    | GotFaqList (Result Http.Error (List Faq))
    | GotFundRaiseStats (Result Http.Error FundRaiseStats)
    | GotBenefitList (Result Http.Error (List Benefit))
    | GotTalentList (Result Http.Error (List Talent))
    | SelectTeamMember Int
    | DotClick Int
    | SwitchTopImage Time.Posix
    | LinkClicked Browser.UrlRequest
    | UrlChanged Url.Url
    | SwitchCategory TalentCategory


init : () -> Url.Url -> Nav.Key -> ( Model, Cmd Msg )
init flags url key =
    ( { navBarClassNames = []
      , serviceContentList = []
      , serviceCategoryList = []
      , jpServiceContentList = []
      , serviceDetailList = []
      , serviceIndex = 0
      , successCaseIndex = 0
      , mediaList = []
      , partnerList = []
      , teamMemberList = []
      , talentList = []
      , selectedTalentCategory = 1
      , selectedTeamMemberIndex = -1
      , articleList = []
      , benefitList = []
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
            { url = "%PUBLIC_URL%/assets/data/service_category.json"
            , expect = Http.expectJson GotServiceCategoryList decodeServiceCategoryList
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
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/benefit.json"
            , expect = Http.expectJson GotBenefitList decodeBenefitList
            }
        , Http.get
            { url = "%PUBLIC_URL%/assets/data/talent.json"
            , expect = Http.expectJson GotTalentList decodeTalentList
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


decodeServiceCategoryList : Decoder (List ServiceCategory)
decodeServiceCategoryList =
    field "data" (list serviceCategoryDecoder)


serviceCategoryDecoder : Decoder ServiceCategory
serviceCategoryDecoder =
    map3 ServiceCategory
        (field "imgSrc" string)
        (field "title" string)
        (field "titleEng" string)


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


decodeBenefitList : Decoder (List Benefit)
decodeBenefitList =
    field "data" (list benefitDecoder)


benefitDecoder : Decoder Benefit
benefitDecoder =
    map3 Benefit
        (field "imgSrc" string)
        (field "title" string)
        (field "description" string)


decodeTalentList : Decoder (List Talent)
decodeTalentList =
    field "data" (list talentDecoder)


talentDecoder : Decoder Talent
talentDecoder =
    map7 Talent
        (field "id" int)
        (field "imgSrc" string)
        (field "field" string)
        (field "fieldEng" string)
        (field "name" string)
        (field "services" (list string))
        (field "intro" string)



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

        SwitchCategory category ->
            case category of
                Marketing ->
                    ( { model | selectedTalentCategory = 1 }, Cmd.none )

                Design ->
                    ( { model | selectedTalentCategory = 2 }, Cmd.none )

                Operation ->
                    ( { model | selectedTalentCategory = 3 }, Cmd.none )

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

        GotServiceCategoryList result ->
            case result of
                Ok serviceCategoryList ->
                    ( { model | serviceCategoryList = serviceCategoryList }, Cmd.none )

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

        GotBenefitList result ->
            case result of
                Ok benefitList ->
                    ( { model | benefitList = benefitList }, Cmd.none )

                Err err ->
                    ( { model | errorMsg = Just err }, Cmd.none )

        GotTalentList result ->
            case result of
                Ok talentList ->
                    ( { model | talentList = talentList }, Cmd.none )

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
            [ a [ id "logo-link", href "/#top" ]
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
                    , a [ href "/service" ] [ text "服務內容" ]
                    , a [ href "/cross-border-sourcing" ] [ text "跨境外包" ]
                    , a [ href "/#faq" ] [ text "常見問題" ]
                    , a [ href "/#article" ] [ text "精選文章" ]
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
                    [ a [ class "consult-btn", href "https://japaninsider.typeform.com/to/dtAK3J", target "_blank" ] [ text "お問い合わせ" ]
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
            [ h2 [] [ text "Japan Insider 是提供日本群眾募資、跨境電商操作、現地網站營運的顧問團隊" ]
            , h1 [ class "top-title" ] [ text "以群眾募資出發，開始日本市場開", br [] [], text "拓之旅!" ]
            , h1 [ class "top-mobile-title" ] [ text "以群眾募資出發，開始日本市場開拓之旅！" ]
            , div [ class "top-section-action-container" ]
                [ a
                    [ class "consult-btn", href "https://japaninsider.typeform.com/to/yvsVAD", target "_blank" ]
                    [ text "免費諮詢" ]
                , a [ class "know-more-btn", href "#service" ] [ text "了解更多服務細節" ]
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
            , h1 [] [ text "海外企業の日本進出する新モデルを創出" ]
            ]
        ]


viewCrossBorderTop : Html Msg
viewCrossBorderTop =
    section [ class "cross-border-top" ]
        [ div [ class "cross-border-hero-description" ]
            [ h2 [] [ text "COMING SOON!" ]
            , h1 [ class "top-title" ] [ text "尋找在日的台灣人才，", br [] [], text "協助你拓展日本市場！" ]
            , p []
                [ span [] [ text "第一個台日線上外包平台" ]
                ]
            ]
        , figure []
            [ img [ class "cross-border-hero-img", Asset.src Asset.talentMatch, alt "hero image" ] [] ]
        ]


viewCrossBorderRegister : Html Msg
viewCrossBorderRegister =
    div [ class "cross-border-register" ] [ p [] [ text "預先登錄，搶先接收平台上線通知" ], a [ class "consult-btn", href "https://japaninsider.typeform.com/to/F9ZOSP" ] [ text "登錄" ] ]


viewCrossBorderBenefit : Model -> Html Msg
viewCrossBorderBenefit { benefitList } =
    section [ class "cross-border-benefit-section" ]
        [ div [ class "cross-border-benefit-content" ]
            [ h2 [] [ text "採用「跨境外包」進入日本市場的好處" ]
            , div [ class "cross-border-benefit-list" ] (List.map viewBenefitItem (List.take 3 benefitList))
            ]
        ]


viewBenefitItem : Benefit -> Html Msg
viewBenefitItem { title, imgSrc, description } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "benefit-item" ]
        [ -- img [ class "benefit-item-image", src imgSrcPath, alt title ] []
          h2 [ class "benefit-item-title" ] [ text title ]
        , p [ class "benefit-item-description" ] [ text description ]
        ]


viewCrossBorderServiceType : Model -> Html Msg
viewCrossBorderServiceType { talentList, selectedTalentCategory } =
    section [ class "cross-border-service-type" ]
        [ h2 [ class "cross-border-promo-title" ] [ text "日本6萬名的台灣海漂族，協助你快速進入日本市場" ]
        , p [ class "cross-border-promo-description" ] [ text "在日本已經超過6萬名的台灣人才，我們都具有多重語言、多重商業文化的背景; 希望透過自己的跨境背景，參與協助海外團隊進入日本市場。" ]
        , h2 [] [ text "尋找人才" ]
        , viewTalent (List.head (List.filter (\talent -> talent.id == selectedTalentCategory) talentList))
        ]


viewTalent : Maybe Talent -> Html Msg
viewTalent maybeTalent =
    case maybeTalent of
        Just talent ->
            let
                imgSrcPath =
                    append assetPath talent.imgSrc
            in
            div [ class "talent-container" ]
                [ div [ class "talent-description" ]
                    [ h3 [] [ text talent.fieldEng ]
                    , h2 [] [ text talent.field ]
                    , div [ class "talent-service-wrapper" ] (List.map (\service -> p [] [ text service ]) talent.services)
                    , div [ class "talent-category" ]
                        [ button
                            [ class
                                (if talent.id == 1 then
                                    "selected"

                                 else
                                    ""
                                )
                            , onClick (SwitchCategory Marketing)
                            ]
                            [ text "行銷" ]
                        , button
                            [ class
                                (if talent.id == 2 then
                                    "selected"

                                 else
                                    ""
                                )
                            , onClick (SwitchCategory Design)
                            ]
                            [ text "設計" ]
                        , button
                            [ class
                                (if talent.id == 3 then
                                    "selected"

                                 else
                                    ""
                                )
                            , onClick (SwitchCategory Operation)
                            ]
                            [ text "營運" ]
                        ]
                    ]
                , div [ class "talent-intro" ]
                    [ figure []
                        [ img [ src imgSrcPath, alt "talent photo", class "talent-img" ] []
                        ]
                    , div [ class "talent-intro-float-wrapper" ]
                        [ h3 [] [ text talent.name ]
                        , p [] [ text talent.intro ]
                        ]
                    ]
                ]

        Nothing ->
            div [] []


viewCrossBorderServiceCategory : Model -> Html Msg
viewCrossBorderServiceCategory { serviceCategoryList } =
    section [ class "cross-border-service-section" ]
        [ h2 [] [ text "人才分類" ]
        , div [ class "cross-border-service-category" ]
            (List.map viewServiceCategory (List.take 8 serviceCategoryList))
        ]


viewServiceCategory : ServiceCategory -> Html Msg
viewServiceCategory { imgSrc, title, titleEng } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "service-category-item" ]
        [ img [ src imgSrcPath, alt title ] []
        , h2 [] [ text title ]
        , p [] [ text titleEng ]
        ]


viewCrossBorderProcess : Html Msg
viewCrossBorderProcess =
    section [ class "cross-border-process-section" ]
        [ h2 [] [ text "使用流程" ]
        , div [ class "flow-chart" ]
            [ div [ class "flow-node", style "z-index" "4" ]
                [ h2 [] [ text "1. 發佈任務" ]
                , p [] [ text "專任人員了解您的需求，在平台上發佈任務，並協助尋找合適的Japan Insider。" ]
                ]
            , div [ class "flow-node", style "z-index" "3" ]
                [ h2 [] [ text "2. 收到報價" ]
                , p [] [ text "從平台上獲得Japan Insider的報價以及提案，在平台上作比較。" ]
                ]
            , div [ class "flow-node", style "z-index" "2" ]
                [ h2 [] [ text "3. 線上溝通" ]
                , p [] [ text "專任人員了解您的需求，在平台上發佈任務，並協助尋找合適的Japan Insider。" ]
                ]
            , div [ class "flow-node", style "z-index" "1" ]
                [ h2 [] [ text "4. 安全付款" ]
                , p [] [ text "在您確認交付的任務完成之後，才會將款項撥予Japan Insider (在日海外工作者)。" ]
                ]
            ]
        ]


viewSectionIntroduction : Model -> Html Msg
viewSectionIntroduction { successStoryList } =
    div [ class "introduction-background-wrapper" ]
        [ section [ id "introduction", class "introduction" ]
            [ h2 []
                [ text "連結後續銷售的群眾募資操作模式"
                ]
            , div [ class "crd-introduction" ]
                [ div [ class "crd-introduction-description" ]
                    [ p [] [ text "群眾募資在日本越來越普及，漸漸成為海外新創進日本市場的前哨站。但群眾募資後的銷售能否持續才是團隊經營的重點，而這跟在群眾募資過程的佈局有很大的關係。Japan Insider著重於連結後續銷售的群眾募資操作模式。" ]
                    ]
                , figure [ class "crd-introduction-figure" ]
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
        [ h2 [ class "section-title" ] [ text "顧問服務內容" ]
        , p [ class "service-subtitle" ] [ text "代理商之外，你有更多的日本進入策略與開拓模式" ]
        , div [ class "service-content-container" ] (List.map viewServiceContent serviceContentList)
        ]


viewJpSectionService { jpServiceContentList } =
    section [ id "service", class "service" ]
        [ h2 [ class "section-title" ] [ text "事業內容" ]
        , div [ class "jp-service-content-container" ] (List.map viewServiceContent jpServiceContentList)
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
            [ span [ class "faq-q" ] [ text "Q: " ]
            , span [] [ text question ]
            ]
        , p
            [ class "faq-answer" ]
            [ text ("A: " ++ answer) ]
        ]


viewSectionArticle : Model -> Html Msg
viewSectionArticle { articleList } =
    section [ id "article", class "article" ]
        [ h2 [ class "section-title" ] [ text "精選文章" ]
        , div [ class "article-container" ] (List.map viewArticle articleList)
        , a [ class "know-more-btn", href "https://www.japaninsider.co/post/post-list/", target "_self" ] [ text "瀏覽更多" ]
        ]


viewArticle : Article -> Html Msg
viewArticle { imgSrc, date, title, link } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    a [ href link, target "_blank", class "link-container" ]
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
            [ h2 [] [ text "跨境外包 - 進入日本市場新選擇" ]
            , p [ class "margin-b-24" ]
                [ span [] [ text "想要有在地小編幫你管理" ]
                , span [ class "font-bold" ] [ text "日本社群" ]
                , span [] [ text "嗎? 想找能投放" ]
                , span [ class "font-bold" ] [ text "日本廣告" ]
                , span [] [ text "，或能協助" ]
                , span [ class "font-bold" ] [ text "日文客服" ]
                , span [] [ text "的中日雙語人才嗎?" ]
                ]
            , p [ class "margin-b-24" ]
                [ span [] [ text "如果沒有足夠的預算聘請全職人員，可以透過跨境外包，尋找日本當地的Freelancer或副業族，讓你" ]
                , span [ class "font-red" ] [ text "在有限資源下，也能立即拓展日本市場!" ]
                ]
            , p [ class "margin-b-24" ] [ text "想了解更多日本市場的跨境外包嗎? 歡迎登錄需求，我們將為您配對合適的現地人才!" ]
            , a [ class "consult-btn", href "https://www.surveycake.com/s/Xvn8m", target "_blank" ] [ text "立即諮詢" ]
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
            , a [ class "consult-btn", href "https://japaninsider.typeform.com/to/dtAK3J", target "_blank" ] [ text "お問い合わせ" ]
            ]
        , figure []
            [ img [ Asset.src Asset.jpEnterpriseRegisterImage, alt "consult as enterprise" ] [] ]
        ]



-- viewSectionTeam : Html Msg
-- viewSectionTeam =
--     div [ id "team", class "team" ]
--         [ h2 [ class "section-title" ] [ text "團隊成員" ]
--         , div [ class "team-description" ]
--             [ h2 []
--                 [ text "JAPAN INSIDER 成員皆位於日本，精通中、日、英文，背景包括數位行銷、產品設計、軟體開發、工程、供應鏈等。讓熟悉日本市場的專業團隊，成為您專案的一份子，協助您進入日本市場。"
--                 ]
--             ]
--         ]


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


viewJpStory : Story -> Html Msg
viewJpStory { link, imgSrc, title, subtitle } =
    let
        imgSrcPath =
            append assetPath imgSrc
    in
    article [ class "jp-story-item" ]
        [ h2 [ class "jp-fund-raise-title" ] [ text subtitle ]
        , img [ class "jp-fund-raise-image", src imgSrcPath, alt title ] []
        , a [ class "know-more-btn", href link, target "_blank" ] [ text "商品ページ" ]
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


viewJpSectionSuccessCase : Model -> Html Msg
viewJpSectionSuccessCase { successStoryList } =
    section [ id "jp-success-case", class "jp-success-case" ]
        [ h2 [ class "section-title" ] [ text "取扱商品・ブランドのピックアップ" ]
        , div [ class "jp-success-crd" ]
            -- TODO @paipo: make carousel and take more items
            (List.map viewJpStory (List.take 4 successStoryList))
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


viewMailChimpSignupForm : Html Msg
viewMailChimpSignupForm =
    div [ id "mc_embed_signup" ]
        [ form [ action "https://japaninsider.us14.list-manage.com/subscribe/post?u=70f47caaa71d96fe967dfa602&id=a8225094be", method "post", id "mc-embedded-subscribe-form", name "mc-embedded-subscribe-form", class "validate", target "_blank", novalidate True ]
            [ div [ id "mc_embed_signup_scroll" ]
                [ h2 [ class "mc_embed_signup--title" ] [ text "預先登錄，搶先接收平台上線通知" ]

                -- , label [ for "mce-EMAIL", class "mc_embed_signup--label" ] [ text "稱呼 (必填)" ]
                -- , input [ class "mc_embed_signup--input-name", type_ "text", name "b_70f47caaa71d96fe967dfa602_a8225094be", placeholder "Jack Wang", value "" ]
                --     []
                -- , label [ for "mce-EMAIL", class "mc_embed_signup--label" ] [ text "Email" ]
                -- , div [ class "mc_embed_signup--input-container" ]
                --     [ input [ type_ "email", value "", name "EMAIL", class "mc_embed_signup--input-email", id "mce-EMAIL", placeholder "abc@gmail.com", required True ]
                --         []
                --     , input [ type_ "submit", value "登錄", name "subscribe", id "mc-embedded-subscribe", class "mc_embed_signup--submit" ]
                --         []
                --     ]
                , a [ class "mc_embed_signup--submit", href "https://japaninsider.typeform.com/to/F9ZOSP" ] [ text "登錄" ]
                ]
            ]
        ]


viewServicePageBody : Html Msg
viewServicePageBody =
    E.layout [] <|
        E.column [ E.width E.fill, E.paddingXY 0 180, E.spacingXY 0 100 ]
            [ E.row [ E.width <| E.px 960, E.spaceEvenly, E.centerX ]
                [ E.column []
                    [ E.paragraph [ Font.size 36, Font.bold, E.paddingXY 0 16 ]
                        [ E.el [ E.alignLeft ] <| E.text "1. 日本"
                        , E.el [ E.alignLeft, Font.color <| E.rgb255 217 74 61 ] <| E.text "群眾募資"
                        , E.el [ E.alignLeft ] <| E.text "執行"
                        ]
                    , E.paragraph
                        [ E.width <| E.px 414
                        , E.paddingEach { top = 0, left = 0, right = 0, bottom = 24 }
                        , E.alignLeft
                        , E.spacing 10
                        , Font.size 18
                        , Font.color <| E.rgb255 99 99 99
                        , Font.alignLeft
                        ]
                        [ E.text "Japan Insider 與各大平台皆有合作，專注於連結後續銷售的群眾募資執行策略。"
                        ]
                    , E.image []
                        { src = "%PUBLIC_URL%/assets/images/japan-crd-service.svg"
                        , description = "crowd sourcing partners"
                        }
                    ]
                , E.column
                    []
                    [ E.image [ E.width <| E.px 312, E.height <| E.px 246 ]
                        { src = "%PUBLIC_URL%/assets/images/service-crdsourcing.svg"
                        , description = "crowd sourcing partners"
                        }
                    ]
                ]
            , E.row [ E.width <| E.px 960, E.spaceEvenly, E.centerX ]
                [ E.column
                    []
                    [ E.image [ E.width <| E.px 370, E.height <| E.px 195 ]
                        { src = "%PUBLIC_URL%/assets/images/service-ec.svg"
                        , description = "crowd sourcing partners"
                        }
                    ]
                , E.column []
                    [ E.paragraph [ Font.size 36, Font.bold, E.paddingXY 0 16 ]
                        [ E.el [ E.alignLeft ] <| E.text "2. 日本亞馬遜等"
                        , E.el [ E.alignLeft, Font.color <| E.rgb255 217 74 61 ] <| E.text "電商操作"
                        ]
                    , E.paragraph
                        [ E.width <| E.px 455
                        , E.paddingEach { top = 0, left = 0, right = 0, bottom = 64 }
                        , E.spacing 10
                        , Font.size 18
                        , Font.color <| E.rgb255 99 99 99
                        , Font.alignLeft
                        ]
                        [ E.text "操作日本亞馬遜等電商平台，無縫接軌群眾募資後的線上銷售。"
                        ]
                    , E.row [ E.centerY, E.spacing 18 ]
                        [ E.image []
                            { src = "%PUBLIC_URL%/assets/images/logo-amazon.svg"
                            , description = "amazon"
                            }
                        , E.image []
                            { src = "%PUBLIC_URL%/assets/images/logo-rakuten.svg"
                            , description = "rakuten"
                            }
                        ]
                    ]
                ]
            , E.row [ E.width <| E.px 960, E.spaceEvenly, E.centerX, E.paddingEach { top = 0, left = 0, right = 0, bottom = 100 } ]
                [ E.column [ E.width <| E.px 450 ]
                    [ E.paragraph [ Font.size 36, Font.bold, E.paddingXY 0 16 ]
                        [ E.el [ E.alignLeft ] <| E.text "3. 日本"
                        , E.el [ E.alignLeft, Font.color <| E.rgb255 217 74 61 ] <| E.text "自有品牌"
                        , E.el [ E.alignLeft ] <| E.text "網站經營"
                        ]
                    , E.paragraph
                        [ E.width <| E.px 387
                        , E.spacing 10
                        , Font.size 18
                        , Font.color <| E.rgb255 99 99 99
                        , Font.alignLeft
                        ]
                        [ E.text "建立團隊自有品牌網站，維持法規、金流、物流等現地營運。"
                        ]
                    ]
                , E.column
                    []
                    [ E.image [ E.width <| E.px 263, E.height <| E.px 211 ]
                        { src = "%PUBLIC_URL%/assets/images/service-exhibition.svg"
                        , description = "exhibition service"
                        }
                    ]
                ]
            , E.row [ E.width E.fill, E.centerX, Background.color <| E.rgb255 255 246 244 ]
                [ E.column [ E.centerX, E.spacing 24, E.padding 40 ]
                    [ E.el [ Font.bold, Font.size 20, E.centerX, E.paddingXY 0 20 ] <| E.text "不只銷售，更讓您的團隊了解日本市場操作、消費者習性!"
                    , E.row [ E.spacing 24 ]
                        [ E.column [ Background.color <| E.rgb255 255 255 255, E.width <| E.px 469, E.height <| E.px 173 ]
                            [ E.paragraph [ E.paddingEach { top = 34, right = 24, bottom = 24, left = 24 }, Font.alignLeft ]
                                [ E.el [ Font.bold ] <| E.text "市場定位"
                                , E.text "測試"
                                ]
                            , E.paragraph [ Font.color <| E.rgb255 99 99 99, E.paddingEach { top = 0, right = 24, bottom = 0, left = 24 }, Font.alignLeft, Font.size 16 ] [ E.text "協助團隊由群眾募資測試市場，由測試結果發展市場定位。" ]
                            ]
                        , E.column [ Background.color <| E.rgb255 255 255 255, E.width <| E.px 469, E.height <| E.px 173 ]
                            [ E.paragraph [ E.paddingEach { top = 34, right = 24, bottom = 24, left = 24 }, Font.alignLeft ]
                                [ E.el [ Font.bold ] <| E.text "行銷計畫"
                                , E.text "執行"
                                ]
                            , E.paragraph [ Font.color <| E.rgb255 99 99 99, E.paddingEach { top = 0, right = 24, bottom = 0, left = 24 }, Font.alignLeft, Font.size 16 ] [ E.text "線上數位廣告、SNS行銷操作、Influencer、PR新聞稿宣傳至少300位媒體等行銷策劃。" ]
                            ]
                        ]
                    , E.row [ E.spacing 24 ]
                        [ E.column [ Background.color <| E.rgb255 255 255 255, E.width <| E.px 469, E.height <| E.px 173 ]
                            [ E.paragraph [ E.paddingEach { top = 34, right = 24, bottom = 24, left = 24 }, Font.alignLeft ]
                                [ E.el [ Font.bold ] <| E.text "在地內容"
                                , E.text "制作"
                                ]
                            , E.paragraph [ Font.color <| E.rgb255 99 99 99, E.paddingEach { top = 0, right = 24, bottom = 0, left = 24 }, Font.alignLeft, Font.size 16 ] [ E.text "針對日本市場消費者特性，在地化行銷文案內容、設計、素材。" ]
                            ]
                        , E.column [ Background.color <| E.rgb255 255 255 255, E.width <| E.px 469, E.height <| E.px 173 ]
                            [ E.paragraph [ E.paddingEach { top = 34, right = 24, bottom = 24, left = 24 }, Font.alignLeft ]
                                [ E.el [ Font.bold ] <| E.text "物流客服"
                                , E.text "策略"
                                ]
                            , E.paragraph [ Font.color <| E.rgb255 99 99 99, E.paddingEach { top = 0, right = 24, bottom = 0, left = 24 }, Font.alignLeft, Font.size 16 ] [ E.text "第一線協助團隊與日本消費者的互動、售後服務以及物流、退換貨的處理。" ]
                            ]
                        ]
                    ]
                ]
            , E.row [ E.width <| E.px 960, E.centerX, E.spaceEvenly ]
                [ E.column [ E.spacing 18 ]
                    [ E.el [ Font.size 36, Font.bold, E.centerX, Font.color <| E.rgb255 1 31 38 ] <| E.text <| "以群眾募資出發，"
                    , E.el
                        [ Font.size 36
                        , Font.bold
                        , Font.center
                        , Font.color <| E.rgb255 1 31 38
                        , E.paddingEach { top = 0, bottom = 32, right = 0, left = 0 }
                        ]
                      <|
                        E.text <|
                            "開始日本市場開拓之旅！"
                    , E.link
                        [ E.width <| E.px 96
                        , E.height <| E.px 48
                        , Background.color <| E.rgb255 217 74 61
                        , Border.rounded 54
                        , Font.color <| E.rgb255 255 255 255
                        , E.centerX
                        ]
                        { url = "https://japaninsider.typeform.com/to/yvsVAD"
                        , label = E.el [ E.centerX ] <| E.text "免費諮詢"
                        }
                    ]
                , E.column []
                    [ E.image [ E.width <| E.px 300, E.height <| E.px 235, Font.size 16 ]
                        { src = "%PUBLIC_URL%/assets/images/earth.svg"
                        , description = "consulting service"
                        }
                    ]
                ]
            ]


type Route
    = Home
    | JpHome
    | CrossBorder
    | ServicePage
    | NotFound


route : Parser (Route -> a) a
route =
    oneOf
        [ map Home top
        , map JpHome (s "jp")
        , map CrossBorder (s "cross-border-sourcing")
        , map ServicePage (s "service")
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
    { title = "Japan Insider"
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

                -- , viewSectionTeam
                , viewFooter
                ]

            JpHome ->
                [ viewJpHeader model
                , viewJpTop
                , viewJpSectionService model
                , viewJpSectionSuccessCase model
                , viewJpSectionEnterpriseRegister
                , viewJpSectionSpirit
                , viewJpSectionSummary
                , viewJpFooter
                ]

            CrossBorder ->
                [ viewHeader model
                , viewCrossBorderTop
                , viewCrossBorderRegister
                , viewCrossBorderBenefit model
                , viewCrossBorderServiceType model
                , viewCrossBorderServiceCategory model
                , viewCrossBorderProcess
                , viewMailChimpSignupForm
                , viewFooter
                ]

            ServicePage ->
                [ viewHeader model
                , viewServicePageBody
                , viewFooter
                ]

            _ ->
                -- Make error page
                [ text "Something Wrong" ]
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
