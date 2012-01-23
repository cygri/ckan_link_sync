This is initial exploratory work on a script for the lodcloud group
and CKAN API.

It is supposed to turn the "links:dbpedia" style extra fields into
proper CKAN relationships.

At the moment it can read and parse the extra fields, but doesn't
write them back yet.

Here's how to create a new package relationship:

curl -i -d '{"comment":"Links: 500"}' http://test.ckan.net/api/rest/dataset/dbpedia/depends_on/freebase -H 'Authorization: YOUR-API-KEY'
