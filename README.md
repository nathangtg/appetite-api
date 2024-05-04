# API README

This system was built and developed using the Laravel framework version 11.X with its supportive environments
such as Sanctums, Middlewares, Laravel and Blade syntax.
On top of that this system is also highly usable in a relational database scheme as everything declared has a relationship.
This API also uses database authentication as its auth driver to enhance security and ease of use

## Introduction

This API serves as the backbone for a comprehensive food ordering system, offering a range of endpoints to facilitate user authentication, user management, restaurant operations, menu handling, and order processing. Built with Laravel, it leverages Sanctum for secure user authentication via token-based authorization.

## Features

-   **User Authentication**: Users can register and log in securely, with passwords hashed for enhanced security.
-   **User Management**: CRUD (Create, Read, Update, Delete) operations are available for managing user accounts. Users can view their own data and update or delete their accounts as needed.

-   **Restaurant Operations**: Restaurant entities can be created, updated, and deleted. Each restaurant has associated menus and orders.

-   **Menu Management**: Menus for each restaurant can be created, updated, and deleted. Users can retrieve lists of available menus for specific restaurants.

-   **Order Processing**: Users can place orders at specific restaurants, with options to create, update, and delete orders. Additionally, users can view their own order history.

## Endpoints

-   **Authentication**:

    -   `POST /auth/register`: Register a new user.
    -   `POST /auth/login`: Log in a user.

-   **User Management**:

    -   `GET /users`: Retrieve a list of users.
    -   `GET /users/{id}`: Retrieve a specific user's details.
    -   `PUT /update/users/{id}`: Update a user's information.
    -   `DELETE /delete/users/{id}`: Delete a user's account.

-   **Restaurant Operations**:

    -   `GET /restaurants`: Retrieve a list of restaurants.
    -   `GET /restaurants/{id}`: Retrieve details of a specific restaurant.
    -   `POST /create/restaurants`: Create a new restaurant.
    -   `PUT /update/restaurants/{id}`: Update restaurant information.
    -   `DELETE /delete/restaurants/{id}`: Delete a restaurant.

-   **Menu Management**:

    -   `GET /menus/{restaurant_id}`: Retrieve menus for a specific restaurant.
    -   `GET /menus/{restaurant_id}/{id}`: Retrieve details of a specific menu.
    -   `POST /menus/{restaurant_id}/create`: Create a new menu for a restaurant.
    -   `PUT /menus/{restaurant_id}/{id}/update`: Update a menu.
    -   `DELETE /menus/{restaurant_id}/{id}/delete`: Delete a menu.

-   **Order Processing**:
    -   `GET /orders/{restaurant_id}`: Retrieve orders for a specific restaurant.
    -   `GET /orders/{restaurant_id}/{id}`: Retrieve details of a specific order.
    -   `GET /orders`: Retrieve the order history of the current user.
    -   `POST /orders/{restaurant_id}/create`: Place a new order.
    -   `PUT /orders/{restaurant_id}/{id}/update`: Update an existing order.
    -   `DELETE /orders/{restaurant_id}/{id}/delete`: Delete an order.

## Usage

1. **Authentication**: Register a new user or log in using the provided endpoints to obtain an authentication token.
2. **User Management**: Access user data, update details, or delete the account if needed.
3. **Restaurant Operations**: Manage restaurants, including creation, update, and deletion.
4. **Menu Management**: Create, update, or delete menus for restaurants.
5. **Order Processing**: Place orders, update existing orders, or view order history.

## Security

-   User passwords are securely hashed to protect sensitive information.
-   Token-based authentication via Sanctum ensures secure access to endpoints.

## Conclusion

This API provides a robust foundation for building a food ordering platform, offering comprehensive functionality for user authentication, restaurant management, menu handling, and order processing. With its intuitive endpoints and secure authentication mechanisms, developers can seamlessly integrate this API into their applications to deliver a seamless food ordering experience for users.
