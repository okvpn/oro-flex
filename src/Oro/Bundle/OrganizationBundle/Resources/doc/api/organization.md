# Oro\Bundle\OrganizationBundle\Entity\Organization

## ACTIONS  

### get

Retrieve a specific organization record.

{@inheritdoc}

### get_list

Retrieve a collection of organization records.

{@inheritdoc}

### create

Create a new organization record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "organizations",
      "attributes": {
         "is_global": null,
         "name": "Acme, South",
         "description": "Example of organization description",
         "enabled": true
      },
      "relationships": {
         "businessUnits": {
            "data": [
               {
                  "type": "businessunits",
                  "id": "1"
               },
               {
                  "type": "businessunits",
                  "id": "2"
               }
            ]
         },
         "users": {
            "data": [
               {
                  "type": "users",
                  "id": "1"
               },
               {
                  "type": "users",
                  "id": "2"
               }
            ]
         }
      }
   }
}
```
{@/request}

### update

Edit a specific organization record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "organizations",
      "id": "2",
      "attributes": {
         "is_global": null,
         "name": "Acme, South",
         "description": "Example of organization description",
         "enabled": true
      },
      "relationships": {
         "businessUnits": {
            "data": [
               {
                  "type": "businessunits",
                  "id": "1"
               },
               {
                  "type": "businessunits",
                  "id": "2"
               }
            ]
         },
         "users": {
            "data": [
               {
                  "type": "users",
                  "id": "1"
               },
               {
                  "type": "users",
                  "id": "2"
               }
            ]
         }
      }
   }
}
```
{@/request}

## FIELDS

### name

#### create

{@inheritdoc}

**The required field.**

#### update 

{@inheritdoc}

**This field must not be empty, if it is passed.**

## SUBRESOURCES

### businessUnits

#### get_subresource

Retrieve the business unit records that belong to a specific organization record.

#### get_relationship

Retrieve the IDs of the business units that belong to a specific organization record.

#### add_relationship

Set the business unit records to a specific organization record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "businessunits",
      "id": "1"
    },
    {
      "type": "businessunits",
      "id": "2"
    },
    {
      "type": "businessunits",
      "id": "3"
    }
  ]
}
```
{@/request}

#### update_relationship

Replace the business units that belong to a specific organization record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "businessunits",
      "id": "1"
    },
    {
      "type": "businessunits",
      "id": "2"
    },
    {
      "type": "businessunits",
      "id": "3"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove the business units that belong to a specific organization record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "businessunits",
      "id": "1"
    }
  ]
}
```
{@/request}

### users

#### get_subresource

Retrieve records of the users who have access to a specific organization.

#### get_relationship

Retrieve the IDs of the users who have access to a specific organization record.

#### add_relationship

Set users who have access to a specific organization.

{@request:json_api}
Example:

```JSON
{
   "data": [
      {
         "type": "users",
         "id": "1"
      },
      {
         "type": "users",
         "id": "2"
      }
   ]
}
```
{@/request}

#### update_relationship

Replace users who have access to a specific organization.

{@request:json_api}
Example:

```JSON
{
   "data": [
      {
         "type": "users",
         "id": "1"
      },
      {
         "type": "users",
         "id": "2"
      }
   ]
}
```
{@/request}

#### delete_relationship

Delete users who have access to a specific organization.

{@request:json_api}
Example:

```JSON
{
   "data": [
      {
         "type": "users",
         "id": "1"
      }
   ]
}
```
{@/request}
