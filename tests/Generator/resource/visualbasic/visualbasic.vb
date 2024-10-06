Imports System.Text.Json.Serialization

' Location of the person
Public Class Location
    <JsonPropertyName("lat")>
    Public Property Lat As Double

    <JsonPropertyName("long")>
    Public Property _Long As Double

End Class

Imports System.Text.Json.Serialization

' An application
Public Class Web
    <JsonPropertyName("name")>
    Public Property Name As String

    <JsonPropertyName("url")>
    Public Property Url As String

End Class

Imports System.Text.Json.Serialization

' An simple author element with some description
Public Class Author
    <JsonPropertyName("title")>
    Public Property Title As String

    <JsonPropertyName("email")>
    Public Property Email As String

    <JsonPropertyName("categories")>
    Public Property Categories As String()

    <JsonPropertyName("locations")>
    Public Property Locations As Location()

    <JsonPropertyName("origin")>
    Public Property Origin As Location

End Class

Imports System.Text.Json.Serialization
Imports System.Collections.Generic
Public Class Meta
    Inherits Dictionary(Of String, String)
End Class

Imports System.Text.Json.Serialization

' An general news entry
Public Class News
    <JsonPropertyName("config")>
    Public Property Config As Meta

    <JsonPropertyName("inlineConfig")>
    Public Property InlineConfig As Dictionary(Of String, String)

    <JsonPropertyName("mapTags")>
    Public Property MapTags As Dictionary(Of String, String)

    <JsonPropertyName("mapReceiver")>
    Public Property MapReceiver As Dictionary(Of String, Author)

    <JsonPropertyName("tags")>
    Public Property Tags As String()

    <JsonPropertyName("receiver")>
    Public Property Receiver As Author()

    <JsonPropertyName("read")>
    Public Property Read As Boolean

    <JsonPropertyName("author")>
    Public Property Author As Author

    <JsonPropertyName("meta")>
    Public Property Meta As Meta

    <JsonPropertyName("sendDate")>
    Public Property SendDate As DateString

    <JsonPropertyName("readDate")>
    Public Property ReadDate As DateAndTime

    <JsonPropertyName("price")>
    Public Property Price As Double

    <JsonPropertyName("rating")>
    Public Property Rating As Integer

    <JsonPropertyName("content")>
    Public Property Content As String

    <JsonPropertyName("question")>
    Public Property Question As String

    <JsonPropertyName("version")>
    Public Property Version As String

    <JsonPropertyName("coffeeTime")>
    Public Property CoffeeTime As TimeString

    <JsonPropertyName("g-recaptcha-response")>
    Public Property Captcha As String

    <JsonPropertyName("media.fields")>
    Public Property MediaFields As String

    <JsonPropertyName("payload")>
    Public Property Payload As Object

End Class
